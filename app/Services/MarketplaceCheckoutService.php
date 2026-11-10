<?php

namespace App\Services;

use App\Contracts\MarketplacePaymentProvider;
use App\Models\Cart;
use App\Models\CheckoutGroup;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MarketplaceCheckoutService
{
    public function __construct(
        private readonly MarketplacePaymentProvider $payments,
        private readonly AuditLogger $audit,
        private readonly MarketplaceNotifier $notifier,
    ) {}

    /** @param array{delivery_method: string, delivery_address?: ?string, recipient_name: string, recipient_phone: string} $data */
    public function checkout(User $buyer, array $data, string $requestKey): CheckoutGroup
    {
        abort_unless($buyer->can('checkout', Cart::class), 403);

        return DB::transaction(function () use ($buyer, $data, $requestKey): CheckoutGroup {
            $existing = CheckoutGroup::query()->where('buyer_id', $buyer->getKey())->where('request_key', $requestKey)->lockForUpdate()->first();
            if ($existing !== null) {
                return $existing->load('orders.items', 'orders.payment');
            }

            $cart = Cart::query()->where('user_id', $buyer->getKey())->where('status', 'open')->lockForUpdate()->firstOrFail();
            $items = $cart->items()->with(['product.seller.user', 'product.category'])->orderBy('product_id')->lockForUpdate()->get();
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
            }

            $products = Product::query()->whereIn('id', $items->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $groups = [];
            $grandTotal = 0;
            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                if ($product === null || ! $product->isPubliclyAvailable()) {
                    throw ValidationException::withMessages(['cart' => 'Ada produk yang sudah tidak tersedia. Muat ulang keranjang.']);
                }
                if ((int) $item->quantity < 1 || (int) $product->stock < (int) $item->quantity) {
                    throw ValidationException::withMessages(['cart' => 'Stok produk '.$product->name.' tidak mencukupi.']);
                }
                if ((int) $product->seller?->user_id === (int) $buyer->getKey()) {
                    throw ValidationException::withMessages(['cart' => 'Keranjang berisi produk dari usaha milik Anda. Hapus produk tersebut sebelum checkout.']);
                }
                if ($product->seller->verification_status !== 'approved' || $product->seller->user->account_status !== 'active') {
                    throw ValidationException::withMessages(['cart' => 'Penjual suatu produk sedang tidak dapat menerima pesanan.']);
                }
                if ($data['payment_method'] === 'xendit' && blank($product->seller->xendit_account_id)) {
                    throw ValidationException::withMessages(['payment_method' => 'Pembayaran satu kali untuk beberapa penjual belum tersedia karena ada penjual yang belum terhubung ke akun Xendit XenPlatform. Pilih COD atau hubungi administrator.']);
                }
                $subtotal = $this->toCents((string) $product->price) * (int) $item->quantity;
                if ($subtotal > 99_999_999_999_999) {
                    throw ValidationException::withMessages(['cart' => 'Total satu produk melampaui batas nilai pesanan.']);
                }
                $groups[$product->seller_id]['seller'] = $product->seller;
                $groups[$product->seller_id]['items'][] = [$item, $product, $subtotal];
                $groups[$product->seller_id]['total'] = ($groups[$product->seller_id]['total'] ?? 0) + $subtotal;
                $grandTotal += $subtotal;
                if ($grandTotal > 99_999_999_999_999) {
                    throw ValidationException::withMessages(['cart' => 'Total checkout melampaui batas nilai yang dapat diproses.']);
                }
            }

            $group = CheckoutGroup::query()->create([
                'buyer_id' => $buyer->getKey(),
                'checkout_number' => 'CHK-'.now()->format('Ymd').'-'.Str::upper(Str::random(10)),
                'request_key' => $requestKey,
                'grand_total' => $this->fromCents($grandTotal),
                'status' => $data['payment_method'] === 'xendit' ? 'awaiting_payment' : 'pending_seller',
            ]);

            foreach ($groups as $sellerId => $sellerGroup) {
                $order = Order::query()->create([
                    'checkout_group_id' => $group->getKey(),
                    'buyer_id' => $buyer->getKey(),
                    'seller_id' => $sellerId,
                    'order_number' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(10)),
                    'total_amount' => $this->fromCents($sellerGroup['total']),
                    'payment_method' => $data['payment_method'] === 'xendit' ? 'gateway' : 'cash',
                    'status' => $data['payment_method'] === 'xendit' ? 'awaiting_payment' : 'pending_seller',
                ]);
                foreach ($sellerGroup['items'] as [$cartItem, $product, $subtotal]) {
                    $order->items()->create([
                        'product_id' => $product->getKey(),
                        'product_name_snapshot' => $product->name,
                        'unit_price' => $product->price,
                        'quantity' => $cartItem->quantity,
                        'subtotal' => $this->fromCents($subtotal),
                        'attributes_snapshot' => ['unit' => $product->unit, 'category' => $product->category->name],
                    ]);
                    $product->decrement('stock', (int) $cartItem->quantity);
                    if ((int) $product->fresh()->stock === 0) {
                        $product->forceFill(['status' => 'out_of_stock'])->save();
                    }
                }
                $initialStatus = $data['payment_method'] === 'xendit' ? 'awaiting_payment' : 'pending_seller';
                $order->statusEvents()->create(['actor_id' => $buyer->getKey(), 'from_status' => null, 'to_status' => $initialStatus, 'created_at' => now()]);
                $address = $data['delivery_method'] === 'delivery' ? trim((string) ($data['delivery_address'] ?? '')) : null;
                $order->delivery()->create([
                    'delivery_method' => $data['delivery_method'],
                    'delivery_address' => $address,
                    'recipient_name' => trim($data['recipient_name']),
                    'recipient_phone' => trim($data['recipient_phone']),
                    'delivery_status' => 'preparing',
                ]);
                if ($data['payment_method'] !== 'xendit') {
                    $this->payments->createCashOnDeliveryPayment($order);
                    $this->notifier->toUser($sellerGroup['seller']->user, 'Pesanan baru', 'Ada pesanan baru yang menunggu keputusan Anda.', 'order', (int) $order->getKey());
                }
            }

            if ($data['payment_method'] === 'xendit') {
                $this->payments->createPendingCheckoutGroupPayment($group);
            } else {
                $this->notifier->toUser($buyer, 'Pesanan dibuat', 'Pesanan telah diteruskan ke penjual. Pembayaran COD tetap menunggu konfirmasi pengumpulan.', 'order', (int) $group->getKey());
            }

            $cart->items()->delete();
            $this->audit->record('marketplace.checkout_created', 'checkout_group', (int) $group->getKey(), ['seller_order_count' => count($groups)]);

            return $group->load('orders.items', 'orders.payment');
        }, 3);
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromCents(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
