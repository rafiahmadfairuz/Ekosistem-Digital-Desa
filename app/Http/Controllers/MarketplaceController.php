<?php

namespace App\Http\Controllers;

use App\Contracts\MarketplacePaymentProvider;
use App\Http\Requests\MarketplaceCheckoutRequest;
use App\Http\Requests\ReviewMarketplaceProductRequest;
use App\Http\Requests\ReviewMarketplaceSellerRequest;
use App\Http\Requests\StoreMarketplaceProductRequest;
use App\Http\Requests\StoreSellerApplicationRequest;
use App\Http\Requests\UpdateMarketplaceCartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CheckoutGroup;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\Seller;
use App\Models\SellerDocument;
use App\Services\AuditLogger;
use App\Services\CheckoutHistoryService;
use App\Services\MarketplaceCheckoutService;
use App\Services\MarketplaceOrderWorkflow;
use App\Services\ProductCatalogService;
use App\Services\SellerOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'integer', 'exists:product_categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'sort' => ['nullable', 'in:newest,price_low,price_high'],
        ]);
        $products = Product::query()->with(['seller', 'category', 'images'])
            ->where('status', 'active')->where('stock', '>', 0)
            ->whereHas('seller', fn ($query) => $query->where('verification_status', 'approved')->whereHas('user', fn ($user) => $user->where('account_status', 'active')))
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->when($request->filled('q'), fn ($query) => $query->where(function ($search) use ($request): void {
                $term = '%'.addcslashes(trim((string) $request->query('q')), '%_\\').'%';
                $search->where('name', 'like', $term)->orWhere('description', 'like', $term);
            }))
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->when($request->filled('min_price'), fn ($query) => $query->where('price', '>=', $request->query('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('price', '<=', $request->query('max_price')))
            ->when($request->query('sort') === 'price_low', fn ($query) => $query->orderBy('price'))
            ->when($request->query('sort') === 'price_high', fn ($query) => $query->orderByDesc('price'))
            ->when(! in_array($request->query('sort'), ['price_low', 'price_high'], true), fn ($query) => $query->latest())
            ->paginate(12)->withQueryString();

        return view('marketplace.index', ['products' => $products, 'categories' => ProductCategory::query()->where('is_active', true)->orderBy('name')->get()]);
    }

    public function showProduct(Product $product)
    {
        $product->load(['seller.user', 'category', 'images']);
        if (! $product->isPubliclyVisible()) {
            $this->authorize('view', $product);
        }

        return view('marketplace.products.show', compact('product'));
    }

    public function productImage(Product $product, ProductImage $image)
    {
        abort_unless((int) $image->product_id === (int) $product->getKey(), 404);
        if (! $product->isPubliclyVisible()) {
            $this->authorize('view', $product);
        }
        abort_unless(Storage::disk('local')->exists($image->private_file_path), 404);

        return Storage::disk('local')->response($image->private_file_path, null, ['Content-Type' => $image->mime_type, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, max-age=300']);
    }

    public function sellerApplication(Request $request)
    {
        abort_unless($request->user()->hasRole('Resident'), 403);
        if (! $request->user()->resident()->exists()) {
            return to_route('profile.create')->with('status', 'Lengkapi profil warga dan pilih wilayah sebelum mendaftar sebagai penjual.');
        }

        $seller = $request->user()->seller()->with('statusEvents')->first();

        return view('marketplace.seller-application', compact('seller'));
    }

    public function submitSellerApplication(StoreSellerApplicationRequest $request, SellerOnboardingService $service)
    {
        $user = $request->user();
        $data = $request->safe()->except('supporting_document');
        if ($user->seller) {
            $service->resubmit($user, $user->seller, $data, $request->file('supporting_document'));
        } else {
            $service->apply($user, $data, $request->file('supporting_document'));
        }

        return to_route('marketplace.seller.application')->with('status', 'Pengajuan usaha tersimpan dan menunggu peninjauan BUMDes.');
    }

    public function sellerProducts(Request $request)
    {
        $this->authorize('create', Product::class);
        $seller = $request->user()->seller;
        $products = $seller->products()->with('category')->latest()->paginate(15);
        $categories = ProductCategory::query()->where('is_active', true)->orderBy('name')->get();

        return view('marketplace.seller.products', compact('products', 'categories'));
    }

    public function editProduct(Request $request, Product $product)
    {
        $this->authorize('update', $product);
        $product->load('images', 'category');
        $categories = ProductCategory::query()->where('is_active', true)->orderBy('name')->get();

        return view('marketplace.seller.product-form', compact('product', 'categories'));
    }

    public function storeProduct(StoreMarketplaceProductRequest $request, ProductCatalogService $service)
    {
        $service->create($request->user(), $request->safe()->except('images'), $request->file('images', []));

        return to_route('marketplace.seller.products')->with('status', 'Produk dikirim untuk pemeriksaan sebelum ditampilkan.');
    }

    public function updateProduct(StoreMarketplaceProductRequest $request, Product $product, ProductCatalogService $service)
    {
        $service->update($request->user(), $product, $request->safe()->except('images'), $request->file('images', []));

        return to_route('marketplace.seller.products')->with('status', 'Perubahan produk dikirim untuk pemeriksaan ulang.');
    }

    public function archiveProduct(Request $request, Product $product, AuditLogger $audit)
    {
        $this->authorize('update', $product);
        DB::transaction(function () use ($product, $request, $audit): void {
            $locked = Product::query()->whereKey($product->getKey())->lockForUpdate()->firstOrFail();
            $old = $locked->status;
            $locked->forceFill(['status' => 'archived'])->save();
            $locked->moderationEvents()->create(['actor_id' => $request->user()->getKey(), 'from_status' => $old, 'to_status' => 'archived', 'created_at' => now()]);
            $audit->record('marketplace.product_archived', 'product', (int) $locked->getKey());
        });

        return back()->with('status', 'Produk diarsipkan dan tidak lagi tampil di katalog.');
    }

    public function deleteProductImage(Request $request, ProductImage $image, ProductCatalogService $service)
    {
        $service->deleteImage($request->user(), $image);

        return back()->with('status', 'Gambar produk dihapus.');
    }

    public function cart(Request $request)
    {
        $this->authorize('checkout', Cart::class);
        $cart = Cart::query()->firstOrCreate(['user_id' => $request->user()->getKey()], ['status' => 'open']);
        $cart->load('items.product.seller.user', 'items.product.category', 'items.product.images');
        $hasOwnProducts = $cart->items->contains(fn (CartItem $item): bool => (int) $item->product?->seller?->user_id === (int) $request->user()->getKey());

        return view('marketplace.cart', compact('cart', 'hasOwnProducts'));
    }

    public function addToCart(Request $request, Product $product)
    {
        $this->authorize('checkout', Cart::class);
        $data = $request->validate(['quantity' => ['required', 'integer', 'between:1,1000']]);
        DB::transaction(function () use ($request, $product, $data): void {
            $cart = Cart::query()->firstOrCreate(['user_id' => $request->user()->getKey()], ['status' => 'open']);
            $cart = Cart::query()->whereKey($cart->getKey())->lockForUpdate()->firstOrFail();
            $locked = Product::query()->whereKey($product->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($locked->isPubliclyAvailable(), 409, 'Produk tidak tersedia.');
            if ((int) $locked->seller?->user_id === (int) $request->user()->getKey()) {
                throw ValidationException::withMessages(['product' => 'Anda tidak dapat memesan produk dari usaha milik sendiri.']);
            }
            $item = CartItem::query()->where('cart_id', $cart->getKey())->where('product_id', $locked->getKey())->lockForUpdate()->first();
            $quantity = (int) ($item?->quantity ?? 0) + (int) $data['quantity'];
            abort_if($quantity > $locked->stock, 422, 'Jumlah melebihi stok tersedia.');
            if ($item) {
                $item->update(['quantity' => $quantity]);
            } else {
                $cart->items()->create(['product_id' => $locked->getKey(), 'quantity' => $quantity]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'product' => $product->name,
                'quantity' => (int) $data['quantity'],
                'message' => 'Barang ditambahkan ke keranjang.',
            ]);
        }

        $returnUrl = url()->previous();
        $returnHost = parse_url($returnUrl, PHP_URL_HOST);
        if (! is_string($returnHost) || strcasecmp($returnHost, $request->getHost()) !== 0) {
            $returnUrl = route('marketplace.products.show', $product);
        }

        return redirect()->to($returnUrl)->with('cart_added', [
            'product' => $product->name,
            'quantity' => (int) $data['quantity'],
        ]);
    }

    public function updateCartItem(UpdateMarketplaceCartRequest $request, CartItem $item)
    {
        $this->authorize('manage', $item->cart);
        DB::transaction(function () use ($request, $item): void {
            $cart = Cart::query()->whereKey($item->cart_id)->lockForUpdate()->firstOrFail();
            $this->authorize('manage', $cart);
            $lockedItem = CartItem::query()->whereKey($item->getKey())->lockForUpdate()->with('cart')->firstOrFail();
            abort_unless((int) $lockedItem->cart_id === (int) $cart->getKey(), 404);
            $product = Product::query()->whereKey($lockedItem->product_id)->lockForUpdate()->firstOrFail();
            abort_unless($product->isPubliclyAvailable() && (int) $request->validated('quantity') <= (int) $product->stock, 422, 'Produk tidak tersedia atau stok tidak mencukupi.');
            if ((int) $product->seller?->user_id === (int) $request->user()->getKey()) {
                throw ValidationException::withMessages(['quantity' => 'Anda tidak dapat memesan produk dari usaha milik sendiri.']);
            }
            $lockedItem->update(['quantity' => $request->validated('quantity')]);
        });

        return back()->with('status', 'Jumlah produk diperbarui.');
    }

    public function removeCartItem(Request $request, CartItem $item)
    {
        $this->authorize('manage', $item->cart);
        DB::transaction(function () use ($item): void {
            $cart = Cart::query()->whereKey($item->cart_id)->lockForUpdate()->firstOrFail();
            $this->authorize('manage', $cart);
            $lockedItem = CartItem::query()->whereKey($item->getKey())->lockForUpdate()->firstOrFail();
            abort_unless((int) $lockedItem->cart_id === (int) $cart->getKey(), 404);
            $lockedItem->delete();
        });

        return back()->with('status', 'Produk dihapus dari keranjang.');
    }

    public function checkoutForm(Request $request)
    {
        $this->authorize('checkout', Cart::class);
        $cart = $request->user()->cart()->with('items.product.seller')->firstOrFail();
        abort_if($cart->items->isEmpty(), 409, 'Keranjang masih kosong.');
        if ($cart->items->contains(fn (CartItem $item): bool => (int) $item->product?->seller?->user_id === (int) $request->user()->getKey())) {
            return to_route('marketplace.cart')->withErrors(['cart' => 'Hapus produk dari usaha milik Anda sebelum checkout.']);
        }

        $sellerAccountsReady = $cart->items->every(fn (CartItem $item): bool => filled($item->product?->seller?->xendit_account_id));
        $payoutPercent = config('services.xendit.marketplace_seller_payout_percent');
        $payoutConfigReady = is_numeric($payoutPercent) && (float) $payoutPercent > 0 && (float) $payoutPercent < 100;
        $providerConfigured = in_array(config('services.xendit.mode'), ['test', 'live'], true)
            && filled(config('services.xendit.secret_key'))
            && filled(config('services.xendit.webhook_token'));
        $xenditEnabled = $providerConfigured && $sellerAccountsReady && $payoutConfigReady;
        $xenditUnavailableReason = ! $providerConfigured
            ? 'Administrator perlu mengatur kunci API, token webhook, dan mode Xendit.'
            : (! $payoutConfigReady
                ? 'Administrator perlu menetapkan persentase pencairan penjual di bawah 100% agar biaya transaksi memiliki saldo.'
                : (! $sellerAccountsReady ? 'Setiap penjual harus terhubung ke sub-account XenPlatform yang sudah diverifikasi.' : null));

        return view('marketplace.checkout', [
            'cart' => $cart,
            'requestKey' => Str::random(64),
            'xenditEnabled' => $xenditEnabled,
            'xenditUnavailableReason' => $xenditUnavailableReason,
            'sellerPayoutPercent' => $payoutConfigReady ? number_format((float) $payoutPercent, 2, ',', '.') : null,
            'platformRetainedPercent' => $payoutConfigReady ? number_format(100 - (float) $payoutPercent, 2, ',', '.') : null,
        ]);
    }

    public function checkout(MarketplaceCheckoutRequest $request, MarketplaceCheckoutService $service)
    {
        $group = $service->checkout($request->user(), $request->validated(), $request->validated('request_key'));
        $message = $request->validated('payment_method') === 'xendit'
            ? 'Satu checkout dibuat untuk seluruh penjual. Selesaikan satu pembayaran Xendit; pesanan baru diteruskan setelah pembayaran dan seluruh alokasi penjual terverifikasi.'
            : 'Pesanan untuk tiap penjual berhasil dibuat. Pembayaran COD masih menunggu konfirmasi yang sah.';

        return to_route('marketplace.orders.group', $group)->with('status', $message);
    }

    public function startPaymentSession(Request $request, Order $order, MarketplacePaymentProvider $payments)
    {
        abort_unless((int) $order->buyer_id === (int) $request->user()->getKey(), 404);
        abort_if($order->checkout_group_id !== null && $order->group?->payment?->checkout_group_id !== null, 409, 'Pembayaran checkout multi-penjual dilakukan satu kali dari halaman bukti checkout.');
        $payment = DB::transaction(function () use ($order, $payments, $request): Payment {
            $lockedOrder = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            abort_unless((int) $lockedOrder->buyer_id === (int) $request->user()->getKey(), 404);
            abort_unless(in_array($lockedOrder->status, ['accepted', 'processing', 'ready_for_delivery', 'in_delivery', 'delivered', 'completed'], true), 409, 'Pembayaran baru tersedia setelah penjual menerima pesanan.');
            $currentPayment = $lockedOrder->payment;
            abort_unless($currentPayment !== null && $currentPayment->payment_method === 'gateway' && $currentPayment->payment_provider === 'xendit', 409, 'Pesanan ini tidak menggunakan pembayaran Xendit.');
            if ($currentPayment->status === 'failed') {
                return $payments->createPendingGatewayPayment($lockedOrder);
            }
            abort_unless($currentPayment->status === 'pending', 409, 'Status pembayaran ini tidak dapat diproses.');

            return $currentPayment;
        }, 3);

        $payment = $payments->createOnlinePaymentSession($payment);
        abort_unless(is_string($payment->checkout_url) && str_starts_with($payment->checkout_url, 'https://'), 502, 'Tautan pembayaran tidak tersedia.');

        return redirect()->away($payment->checkout_url);
    }

    public function startCheckoutGroupPaymentSession(Request $request, CheckoutGroup $group, MarketplacePaymentProvider $payments)
    {
        abort_unless((int) $group->buyer_id === (int) $request->user()->getKey(), 404);
        $group->load('payment');
        $payment = $group->payment;
        abort_unless($group->status === 'awaiting_payment' && $payment !== null
            && $payment->payment_provider === 'xendit' && $payment->payment_method === 'gateway'
            && $payment->status === 'pending', 409, 'Checkout ini tidak sedang menunggu pembayaran Xendit.');
        $payment = $payments->createOnlinePaymentSession($payment);
        abort_unless(is_string($payment->checkout_url) && str_starts_with($payment->checkout_url, 'https://'), 502, 'Tautan pembayaran tidak tersedia.');

        return redirect()->away($payment->checkout_url);
    }

    public function orders(Request $request)
    {
        $orders = Order::query()->with(['seller', 'items', 'payment', 'group.payment', 'delivery'])->where('buyer_id', $request->user()->getKey())
            ->whereHas('group', fn ($query) => $query->whereNull('buyer_archived_at'))
            ->latest()->paginate(15);

        return view('marketplace.orders.index', ['orders' => $orders, 'sellerView' => false]);
    }

    public function sellerOrders(Request $request)
    {
        abort_unless($request->user()->hasRole('Seller') && $request->user()->seller !== null, 403);
        $orders = $request->user()->seller->orders()->where('status', '!=', 'awaiting_payment')->with(['buyer', 'items', 'payment', 'group.payment', 'delivery'])->latest()->paginate(15);

        return view('marketplace.orders.index', ['orders' => $orders, 'sellerView' => true]);
    }

    public function showOrder(Request $request, Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['group.payment.events', 'group.payment.allocations', 'group.orders.payment', 'seller.user', 'buyer', 'items', 'payment.events', 'delivery.events', 'statusEvents']);
        $viewerIsSeller = (int) $order->seller->user_id === (int) $request->user()->getKey();
        $groupIsFinal = $this->isFinalCheckout($order->group);

        return view('marketplace.orders.show', compact('order', 'viewerIsSeller', 'groupIsFinal'));
    }

    public function showCheckoutGroup(Request $request, CheckoutGroup $group)
    {
        abort_unless((int) $group->buyer_id === (int) $request->user()->getKey(), 404);
        $group->load(['payment.allocations', 'orders.seller', 'orders.items', 'orders.payment', 'orders.delivery']);
        $isFinal = $this->isFinalCheckout($group);
        $canArchive = $this->canArchiveCheckout($group);

        return view('marketplace.orders.group', compact('group', 'isFinal', 'canArchive'));
    }

    public function checkoutReceipt(Request $request, CheckoutGroup $group)
    {
        abort_unless((int) $group->buyer_id === (int) $request->user()->getKey(), 404);
        $group->load(['payment.allocations', 'buyer', 'orders.seller', 'orders.items', 'orders.payment', 'orders.delivery']);
        $isFinal = $this->isFinalCheckout($group);

        return view('marketplace.orders.receipt', compact('group', 'isFinal'));
    }

    public function archiveCheckoutHistory(Request $request, CheckoutGroup $group, CheckoutHistoryService $service)
    {
        $service->archiveForBuyer($request->user(), $group);

        return to_route('marketplace.orders.index')->with('status', 'Checkout ditutup dan disembunyikan dari riwayat Anda. Catatan transaksi tetap disimpan.');
    }

    private function isFinalCheckout(CheckoutGroup $group): bool
    {
        $groupPayment = $group->payment;

        return $group->orders->isNotEmpty() && $group->orders->every(fn (Order $order): bool => $order->status === 'completed')
            && ($groupPayment?->status === 'paid' || $group->orders->every(fn (Order $order): bool => $order->payment?->status === 'paid'));
    }

    private function canArchiveCheckout(CheckoutGroup $group): bool
    {
        return $group->buyer_archived_at === null
            && $group->orders->isNotEmpty()
            && $group->orders->every(fn (Order $order): bool => in_array($order->status, ['completed', 'cancelled', 'rejected'], true))
            && ($group->payment?->status === 'paid'
                || $group->orders->every(fn (Order $order): bool => $order->status !== 'completed' || $order->payment?->status === 'paid'));
    }

    public function cancelOrder(Request $request, Order $order, MarketplaceOrderWorkflow $workflow)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']]);
        $workflow->cancelByBuyer($request->user(), $order, $data['reason']);

        return back()->with('status', 'Pesanan dibatalkan dan stok dikembalikan.');
    }

    public function confirmReceipt(Request $request, Order $order, MarketplaceOrderWorkflow $workflow)
    {
        $workflow->confirmReceipt($request->user(), $order);

        return back()->with('status', 'Penerimaan pesanan dicatat.');
    }

    public function sellerDecision(Request $request, Order $order, MarketplaceOrderWorkflow $workflow)
    {
        $data = $request->validate(['decision' => ['required', 'in:accepted,rejected'], 'reason' => ['nullable', 'string', 'max:1000', 'required_if:decision,rejected']]);
        $workflow->sellerDecision($request->user(), $order, $data['decision'], $data['reason'] ?? null);

        return back()->with('status', $data['decision'] === 'accepted' ? 'Pesanan diterima.' : 'Pesanan ditolak dan stok dikembalikan.');
    }

    public function advanceOrder(Request $request, Order $order, MarketplaceOrderWorkflow $workflow)
    {
        $data = $request->validate(['status' => ['required', 'in:processing,ready_for_delivery']]);
        $workflow->advanceOrder($request->user(), $order, $data['status']);

        return back()->with('status', 'Tahap persiapan pesanan diperbarui.');
    }

    public function updateDelivery(Request $request, Order $order, MarketplaceOrderWorkflow $workflow)
    {
        $data = $request->validate(['status' => ['required', 'in:in_delivery,delivered,failed'], 'reference' => ['nullable', 'string', 'max:100'], 'note' => ['nullable', 'string', 'max:1000']]);
        $workflow->updateDelivery($request->user(), $order, $data['status'], $data['reference'] ?? null, $data['note'] ?? null);

        return back()->with('status', 'Status pengiriman diperbarui.');
    }

    public function confirmCod(Request $request, Payment $payment, MarketplaceOrderWorkflow $workflow)
    {
        $workflow->confirmCodCollection($request->user(), $payment);

        return back()->with('status', 'Pembayaran COD tercatat setelah pesanan diterima.');
    }

    public function sellerApplications(Request $request)
    {
        $this->authorize('viewAny', Seller::class);
        $applications = Seller::query()->with(['user', 'documents', 'statusEvents.actor'])
            ->whereIn('verification_status', ['pending', 'under_review', 'rejected'])
            ->orderByRaw("CASE verification_status WHEN 'pending' THEN 0 WHEN 'under_review' THEN 1 ELSE 2 END")
            ->latest()->paginate(15, ['*'], 'application_page');
        $otherSellers = Seller::query()->with(['user', 'documents', 'statusEvents.actor'])
            ->whereIn('verification_status', ['approved', 'suspended', 'inactive'])
            ->latest('reviewed_at')->paginate(15, ['*'], 'seller_page');

        return view('marketplace.management.sellers', compact('applications', 'otherSellers'));
    }

    public function xenditSellerAccounts(Request $request)
    {
        abort_unless($request->user()->hasRole('Super Admin'), 403);

        return view('admin.marketplace.xendit-seller-accounts', [
            'sellers' => Seller::query()->with('user')->where('verification_status', 'approved')->orderBy('business_name')->paginate(25),
        ]);
    }

    public function updateXenditSellerAccount(Request $request, Seller $seller, AuditLogger $audit)
    {
        abort_unless($request->user()->hasRole('Super Admin'), 403);
        abort_unless($seller->verification_status === 'approved', 404);
        $data = $request->validate([
            'xendit_account_id' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);
        $accountId = isset($data['xendit_account_id']) ? trim($data['xendit_account_id']) : null;
        $seller->forceFill(['xendit_account_id' => $accountId === '' ? null : $accountId])->save();
        $audit->record('marketplace.seller_xendit_account_updated', 'seller', (int) $seller->getKey(), ['configured' => $accountId !== null && $accountId !== '']);

        return back()->with('status', $accountId ? 'Sub-account Xendit penjual disimpan.' : 'Sub-account Xendit penjual dilepas.');
    }

    public function reviewSeller(ReviewMarketplaceSellerRequest $request, Seller $seller, SellerOnboardingService $service)
    {
        $service->review($request->user(), $seller, $request->validated('status'), $request->validated('reason'));

        return back()->with('status', 'Status pengajuan penjual diperbarui.');
    }

    public function sellerDocument(Request $request, Seller $seller, SellerDocument $document)
    {
        $this->authorize('view', $seller);
        abort_unless((int) $document->seller_id === (int) $seller->getKey(), 404);
        abort_unless(Storage::disk('local')->exists($document->private_file_path), 404);

        return Storage::disk('local')->download($document->private_file_path, 'dokumen-penjual.'.$this->safeExtension($document->mime_type));
    }

    public function categories(Request $request)
    {
        $this->authorize('moderate', Product::class);

        return view('marketplace.management.categories', ['categories' => ProductCategory::query()->withCount('products')->orderBy('name')->paginate(25)]);
    }

    public function storeCategory(Request $request, AuditLogger $audit)
    {
        $this->authorize('moderate', Product::class);
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:product_categories,name'], 'description' => ['nullable', 'string', 'max:2000']]);
        $category = ProductCategory::query()->create(['name' => trim($data['name']), 'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(6)), 'description' => $data['description'] ?? null, 'is_active' => true]);
        $audit->record('marketplace.category_created', 'product_category', (int) $category->getKey());

        return back()->with('status', 'Kategori dibuat.');
    }

    public function updateCategory(Request $request, ProductCategory $category, AuditLogger $audit)
    {
        $this->authorize('moderate', Product::class);
        $data = $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('product_categories', 'name')->ignore($category->getKey())], 'description' => ['nullable', 'string', 'max:2000'], 'is_active' => ['required', 'boolean']]);
        $category->update(['name' => trim($data['name']), 'description' => $data['description'] ?? null, 'is_active' => $data['is_active']]);
        $audit->record('marketplace.category_updated', 'product_category', (int) $category->getKey());

        return back()->with('status', 'Kategori diperbarui.');
    }

    public function moderateProducts(Request $request)
    {
        $this->authorize('moderate', Product::class);
        $products = Product::query()->with(['seller.user', 'category', 'images'])->whereIn('status', ['pending_review', 'active', 'out_of_stock'])->latest()->paginate(20);

        return view('marketplace.management.products', compact('products'));
    }

    public function reviewProduct(ReviewMarketplaceProductRequest $request, Product $product, ProductCatalogService $service)
    {
        $service->moderate($request->user(), $product, $request->validated('status'), $request->validated('reason'));

        return back()->with('status', 'Status produk diperbarui.');
    }

    private function safeExtension(string $mime): string
    {
        return match ($mime) {
            'application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png', default => 'bin',
        };
    }
}
