<?php

namespace App\Services;

use App\Contracts\MarketplacePaymentProvider;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CheckoutGroup;
use App\Models\Delivery;
use App\Models\DeliveryEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusEvent;
use App\Models\Product;
use App\Models\ProductModerationEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartCheckoutService
{
    public function __construct(private readonly AuditLogger $audit, private readonly MarketplaceNotifier $notifier, private readonly MarketplacePaymentProvider $payments) {}

    public function add(User $buyer, Product $product, int $quantity): Cart
    {
        $this->assertBuyer($buyer);
        abort_unless($quantity > 0 && $quantity <= 100, 422, 'Jumlah barang tidak valid.');

        return DB::transaction(function () use ($buyer, $product, $quantity): Cart {
            $cart = $this->cartForUpdate($buyer);
            $lockedProduct = Product::query()->whereKey($product->getKey())->lockForUpdate()->with(['seller', 'category'])->firstOrFail();
            $this->assertPurchasable($lockedProduct, $quantity, $buyer);
            $item = $cart->items()->where('product_id', $lockedProduct->getKey())->lockForUpdate()->first();
            $nextQuantity = (int) ($item?->quantity ?? 0) + $quantity;
            $this->assertPurchasable($lockedProduct, $nextQuantity, $buyer);

            if ($item === null) {
                $cart->items()->create(['product_id' => $lockedProduct->getKey(), 'quantity' => $quantity]);
            } else {
                $item->update(['quantity' => $nextQuantity]);
            }

            return $cart->load('items.product.seller');
        });
    }

    public function updateQuantity(User $buyer, CartItem $item, int $quantity): Cart
    {
        abort_unless($quantity > 0 && $quantity <= 100, 422, 'Jumlah barang tidak valid.');
        $cart = $item->cart;
        abort_unless($cart !== null && (int) $cart->user_id === (int) $buyer->getKey(), 404);
        abort_unless($buyer->can('manage', $cart), 403);

        return DB::transaction(function () use ($buyer, $item, $quantity, $cart): Cart {
            $lockedCart = Cart::query()->whereKey($cart->getKey())->lockForUpdate()->firstOrFail();
            $lockedItem = $lockedCart->items()->whereKey($item->getKey())->lockForUpdate()->with('product.seller', 'product.category')->firstOrFail();
            abort_unless($lockedItem->product !== null, 409, 'Produk tidak lagi tersedia. Hapus dari keranjang.');
            $this->assertPurchasable($lockedItem->product, $quantity, $buyer);
            $lockedItem->update(['quantity' => $quantity]);

            return $lockedCart->load('items.product.seller');
        });
    }

    public function removeItem(User $buyer, CartItem $item): void
    {
        $cart = $item->cart;
        abort_unless($cart !== null && (int) $cart->user_id === (int) $buyer->getKey(), 404);
        abort_unless($buyer->can('manage', $cart), 403);
        $item->delete();
    }

    /** @param array{request_key: string, payment_method: string, delivery_method: string, delivery_address?: ?string, recipient_name?: ?string, recipient_phone?: ?string} $data */
    public function checkout(User $buyer, array $data): CheckoutGroup
    {
        $this->assertBuyer($buyer);
        abort_unless($buyer->can('checkout', Cart::class), 403);
        if ($data['payment_method'] !== 'cod') {
            throw ValidationException::withMessages(['payment_method' => 'Saat ini hanya tersedia pembayaran tunai saat barang diterima (COD).']);
        }

        return DB::transaction(function () use ($buyer, $data): CheckoutGroup {
            $cart = $this->cartForUpdate($buyer);
            $existing = CheckoutGroup::query()->where('buyer_id', $buyer->getKey())->where('request_key', $data['request_key'])->with('orders')->first();
            if ($existing !== null) {
                return $existing;
            }

            $cartItems = $cart->items()->orderBy('product_id')->get();
            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
            }

            $products = [];
            foreach ($cartItems as $cartItem) {
                $product = Product::query()->whereKey($cartItem->product_id)->lockForUpdate()->with(['seller', 'category'])->first();
                if ($product === null) {
                    throw ValidationException::withMessages(['cart' => 'Ada produk yang sudah dihapus. Perbarui keranjang sebelum melanjutkan.']);
                }
                $this->assertPurchasable($product, (int) $cartItem->quantity, $buyer);
                $products[$product->getKey()] = $product;
            }

            $totalCents = 0;
            $orders = [];
            $grouped = $cartItems->groupBy(fn (CartItem $item): int => (int) $products[$item->product_id]->seller_id);
            foreach ($grouped as $sellerId => $items) {
                $subtotalCents = 0;
                foreach ($items as $cartItem) {
                    $product = $products[$cartItem->product_id];
                    $subtotalCents += $this->moneyCents($product->price) * (int) $cartItem->quantity;
                }
                $totalCents += $subtotalCents;
                $orders[(int) $sellerId] = ['items' => $items, 'subtotal_cents' => $subtotalCents];
            }

            $group = CheckoutGroup::query()->create([
                'buyer_id' => $buyer->getKey(),
                'checkout_number' => 'CHK-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'request_key' => $data['request_key'],
                'grand_total' => $totalCents / 100,
                'status' => 'pending',
            ]);

            foreach ($orders as $sellerId => $orderData) {
                $order = Order::query()->create([
                    'checkout_group_id' => $group->getKey(), 'buyer_id' => $buyer->getKey(),
                    'seller_id' => $sellerId,
                    'order_number' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                    'total_amount' => $orderData['subtotal_cents'] / 100,
                    'payment_method' => 'cod', 'status' => 'pending_seller',
                ]);
                foreach ($orderData['items'] as $cartItem) {
                    $product = $products[$cartItem->product_id];
                    $quantity = (int) $cartItem->quantity;
                    OrderItem::query()->create([
                        'order_id' => $order->getKey(), 'product_id' => $product->getKey(),
                        'product_name_snapshot' => $product->name,
                        'unit_price' => $product->price,
                        'quantity' => $quantity,
                        'subtotal' => $this->moneyCents($product->price) * $quantity / 100,
                        'attributes_snapshot' => ['unit' => $product->unit],
                    ]);
                    $wasInStock = (int) $product->stock > 0;
                    $product->decrement('stock', $quantity);
                    if ($wasInStock && (int) $product->fresh()->stock === 0) {
                        $product->forceFill(['status' => 'out_of_stock'])->save();
                        ProductModerationEvent::query()->create([
                            'product_id' => $product->getKey(), 'actor_id' => null,
                            'from_status' => 'active', 'to_status' => 'out_of_stock',
                            'reason' => 'Stok habis setelah checkout.', 'created_at' => now(),
                        ]);
                    }
                }
                OrderStatusEvent::query()->create([
                    'order_id' => $order->getKey(), 'actor_id' => $buyer->getKey(),
                    'from_status' => null, 'to_status' => 'pending_seller', 'created_at' => now(),
                ]);
                $delivery = Delivery::query()->create([
                    'order_id' => $order->getKey(),
                    'delivery_method' => $data['delivery_method'],
                    'delivery_address' => $data['delivery_method'] === 'delivery' ? ($data['delivery_address'] ?? null) : null,
                    'recipient_name' => $data['delivery_method'] === 'delivery' ? ($data['recipient_name'] ?? null) : null,
                    'recipient_phone' => $data['delivery_method'] === 'delivery' ? ($data['recipient_phone'] ?? null) : null,
                    'delivery_status' => 'preparing',
                ]);
                DeliveryEvent::query()->create([
                    'delivery_id' => $delivery->getKey(), 'actor_id' => $buyer->getKey(),
                    'from_status' => null, 'to_status' => 'preparing', 'created_at' => now(),
                ]);
                $this->payments->createCashOnDeliveryPayment($order);
                $order->load('seller.user');
                $this->notifier->toUser($order->seller->user, 'Pesanan baru', 'Ada pesanan baru yang menunggu keputusan Anda.', 'order', (int) $order->getKey());
                $this->notifier->toUser($buyer, 'Pesanan dibuat', 'Pesanan telah diteruskan ke penjual. Pembayaran COD tetap menunggu konfirmasi pengumpulan.', 'order', (int) $order->getKey());
                $this->audit->record('marketplace.order_created', 'order', (int) $order->getKey(), ['checkout_group_id' => (int) $group->getKey(), 'seller_id' => (int) $sellerId]);
            }

            $cart->items()->delete();
            $cart->touch();

            return $group->load('orders.seller');
        }, 3);
    }

    private function cartForUpdate(User $buyer): Cart
    {
        Cart::query()->firstOrCreate(['user_id' => $buyer->getKey()], ['status' => 'open']);

        return Cart::query()->where('user_id', $buyer->getKey())->lockForUpdate()->firstOrFail();
    }

    private function assertPurchasable(Product $product, int $quantity, User $buyer): void
    {
        abort_unless($product->status === 'active'
            && $product->seller?->verification_status === 'approved'
            && $product->category?->is_active === true, 409, 'Produk tidak lagi tersedia untuk dibeli.');
        if ((int) $product->seller?->user_id === (int) $buyer->getKey()) {
            throw ValidationException::withMessages(['cart' => 'Anda tidak dapat memesan produk dari usaha milik sendiri.']);
        }
        if ($quantity < 1 || $quantity > (int) $product->stock) {
            throw ValidationException::withMessages(['quantity' => 'Stok tidak mencukupi. Perbarui jumlah barang.']);
        }
    }

    private function assertBuyer(User $buyer): void
    {
        abort_unless($buyer->account_status === 'active' && $buyer->hasRole('Resident'), 403);
    }

    private function moneyCents(string|int|float $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
