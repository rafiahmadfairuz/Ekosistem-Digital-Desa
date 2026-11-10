<?php

namespace Tests\Feature;

use App\Contracts\MarketplacePaymentProvider;
use App\Models\BumdesStaff;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CheckoutGroup;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Region;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Seller;
use App\Models\User;
use App\Models\Village;
use App\Services\MarketplaceWebhookProcessor;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_multi_seller_checkout_creates_snapshot_orders_and_reserves_stock_atomically(): void
    {
        $buyer = $this->userWithRoles(['Resident']);
        [$sellerA, $productA] = $this->approvedSellerWithProduct('Seller A', 'Kopi lokal', '12500.00', 6);
        [, $productB] = $this->approvedSellerWithProduct('Seller B', 'Gula aren', '8000.00', 4);

        $this->actingAs($buyer)->post(route('marketplace.cart.add', $productA), ['quantity' => 2])->assertRedirect();
        $this->post(route('marketplace.cart.add', $productB), ['quantity' => 1])->assertRedirect();
        $key = Str::random(64);
        $checkout = ['delivery_method' => 'delivery', 'delivery_address' => 'Jalan Melati RT 01/RW 02', 'recipient_name' => 'Warga Pembeli', 'recipient_phone' => '081234567890', 'request_key' => $key, 'buyer_id' => 99999, 'seller_id' => 99999, 'total_amount' => '1.00', 'price' => '0.01', 'payment_status' => 'paid'];
        $this->post(route('marketplace.checkout.store'), $checkout)->assertRedirect();

        $group = CheckoutGroup::query()->firstOrFail();
        $this->assertCount(2, $group->orders);
        $this->assertSame('33000.00', $group->grand_total);
        $this->assertDatabaseHas('order_items', ['product_id' => $productA->id, 'product_name_snapshot' => 'Kopi lokal', 'unit_price' => '12500.00', 'quantity' => 2, 'subtotal' => '25000.00']);
        $this->assertSame(4, $productA->fresh()->stock);
        $this->assertSame(3, $productB->fresh()->stock);
        $this->assertDatabaseHas('payments', ['order_id' => $group->orders->first()->id, 'invoice_id' => null, 'status' => 'pending', 'payment_provider' => 'cod']);
        $this->assertDatabaseHas('deliveries', ['order_id' => $group->orders->first()->id, 'delivery_method' => 'delivery']);
        $this->assertSame(0, CartItem::query()->count());

        $this->post(route('marketplace.checkout.store'), $checkout)->assertRedirect();
        $this->assertDatabaseCount('checkout_groups', 1);
        $this->assertDatabaseCount('orders', 2);
        $this->assertSame(4, $productA->fresh()->stock);
    }

    public function test_add_to_cart_returns_to_product_page_with_confirmation(): void
    {
        $buyer = $this->userWithRoles(['Resident']);
        [, $product] = $this->approvedSellerWithProduct('Warung Kembali', 'Dodol desa', '12000.00', 4);

        $this->actingAs($buyer)->get(route('marketplace.products.show', $product))->assertOk();

        $this->post(route('marketplace.cart.add', $product), ['quantity' => 2])
            ->assertRedirect(route('marketplace.products.show', $product))
            ->assertSessionHas('cart_added', ['product' => 'Dodol desa', 'quantity' => 2]);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $buyer->cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $this->get(route('marketplace.products.show', $product))
            ->assertOk()
            ->assertSee('Dodol desa')
            ->assertSee('data-cart-added-toast', false)
            ->assertSee('Lihat keranjang');
    }

    public function test_seller_cannot_add_or_checkout_products_from_their_own_shop(): void
    {
        [$seller, $product] = $this->approvedSellerWithProduct('Toko Sendiri', 'Kue Warga', '15000.00', 5);
        $seller->roles()->attach(Role::query()->where('name', 'Resident')->value('id'));
        $seller->load('roles');

        $this->actingAs($seller)
            ->post(route('marketplace.cart.add', $product), ['quantity' => 1])
            ->assertSessionHasErrors('product');
        $this->assertDatabaseCount('cart_items', 0);

        $cart = Cart::query()->create(['user_id' => $seller->getKey(), 'status' => 'open']);
        $cart->items()->create(['product_id' => $product->getKey(), 'quantity' => 1]);
        $this->post(route('marketplace.checkout.store'), [
            'delivery_method' => 'pickup',
            'recipient_name' => 'Penjual',
            'recipient_phone' => '08123456789',
            'request_key' => Str::random(64),
        ])->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('checkout_groups', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $cart->getKey(), 'product_id' => $product->getKey()]);
    }

    public function test_checkout_receipt_is_printable_and_visible_only_to_its_buyer(): void
    {
        $buyer = $this->userWithRoles(['Resident']);
        [, $product] = $this->approvedSellerWithProduct('Warung Melati', 'Keripik singkong', '12000.00', 4);
        $this->actingAs($buyer)->post(route('marketplace.cart.add', $product), ['quantity' => 2])->assertRedirect();
        $this->post(route('marketplace.checkout.store'), [
            'delivery_method' => 'pickup',
            'recipient_name' => 'Warga Pembeli',
            'recipient_phone' => '08123456789',
            'request_key' => Str::random(64),
        ])->assertRedirect();

        $group = CheckoutGroup::query()->firstOrFail();
        $this->get(route('marketplace.orders.group.receipt', $group))
            ->assertOk()
            ->assertSee('Bukti checkout')
            ->assertSee($group->checkout_number)
            ->assertSee('Warung Melati')
            ->assertSee('Keripik singkong')
            ->assertSee('Rp24.000')
            ->assertSee('bukan bukti pembayaran')
            ->assertSee('window.print()');

        $order = $group->orders()->firstOrFail();
        $order->forceFill(['status' => 'completed', 'completed_at' => now()])->save();
        $order->payment()->update(['status' => 'paid', 'paid_at' => now()]);
        $this->get(route('marketplace.orders.group.receipt', $group->fresh()))
            ->assertOk()
            ->assertSee('Struk final pembelian')
            ->assertSee('Lunas tercatat');

        $otherBuyer = $this->userWithRoles(['Resident']);
        $this->actingAs($otherBuyer)->get(route('marketplace.orders.group.receipt', $group))->assertNotFound();
    }

    public function test_buyer_can_hide_only_closed_checkout_without_deleting_financial_history(): void
    {
        $buyer = $this->userWithRoles(['Resident']);
        [, $product] = $this->approvedSellerWithProduct('Toko Uji', 'Barang uji', '10000.00', 3);
        $this->actingAs($buyer)->post(route('marketplace.cart.add', $product), ['quantity' => 1])->assertRedirect();
        $this->post(route('marketplace.checkout.store'), [
            'delivery_method' => 'pickup',
            'recipient_name' => 'Pembeli Uji',
            'recipient_phone' => '08123456789',
            'request_key' => Str::random(64),
        ])->assertRedirect();

        $group = CheckoutGroup::query()->firstOrFail();
        $this->delete(route('marketplace.orders.group.history.destroy', $group))->assertSessionHasErrors('history');
        $this->assertNull($group->fresh()->buyer_archived_at);

        $order = $group->orders()->firstOrFail();
        $order->forceFill(['status' => 'completed', 'completed_at' => now()])->save();
        $order->payment()->update(['status' => 'paid', 'paid_at' => now()]);
        $this->delete(route('marketplace.orders.group.history.destroy', $group))->assertRedirect(route('marketplace.orders.index'));

        $this->assertNotNull($group->fresh()->buyer_archived_at);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'paid']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'marketplace.checkout_history_archived', 'entity_id' => $group->id]);
        $this->get(route('marketplace.orders.index'))->assertOk()->assertDontSee($order->order_number);

        $otherBuyer = $this->userWithRoles(['Resident']);
        $this->actingAs($otherBuyer)->delete(route('marketplace.orders.group.history.destroy', $group))->assertNotFound();
    }

    public function test_buyer_and_seller_cannot_read_other_users_orders_and_pending_seller_cannot_publish(): void
    {
        $buyer = $this->userWithRoles(['Resident']);
        $otherBuyer = $this->userWithRoles(['Resident']);
        [$seller, $product] = $this->approvedSellerWithProduct('Seller', 'Madu', '20000.00', 2);
        $otherSeller = $this->userWithRoles(['Seller']);
        $otherProfile = Seller::query()->create(['user_id' => $otherSeller->id, 'business_name' => 'Toko lain', 'verification_status' => 'approved']);
        $otherProduct = Product::query()->create(['seller_id' => $otherProfile->id, 'category_id' => $product->category_id, 'name' => 'Produk lain', 'slug' => 'produk-lain', 'description' => 'Produk', 'unit' => 'buah', 'price' => '1000.00', 'stock' => 1, 'status' => 'active']);

        $this->actingAs($buyer)->post(route('marketplace.cart.add', $product), ['quantity' => 1])->assertRedirect();
        $this->post(route('marketplace.checkout.store'), ['delivery_method' => 'pickup', 'recipient_name' => 'Pembeli', 'recipient_phone' => '08123456789', 'request_key' => Str::random(64)])->assertRedirect();
        $order = Order::query()->firstOrFail();
        $this->actingAs($otherBuyer)->get(route('marketplace.orders.show', $order))->assertForbidden();
        $this->actingAs($otherSeller)->get(route('marketplace.orders.show', $order))->assertForbidden();
        $this->actingAs($seller)->get(route('marketplace.orders.show', $order))->assertOk();
        $this->assertTrue($otherProduct->isPubliclyAvailable());

        $pending = $this->userWithRoles(['Seller']);
        Seller::query()->create(['user_id' => $pending->id, 'business_name' => 'Menunggu', 'verification_status' => 'pending']);
        $this->actingAs($pending)->post(route('marketplace.seller.products.store'), [])->assertForbidden();
    }

    public function test_seller_application_requires_an_authorized_reviewer_and_approval_grants_seller_role(): void
    {
        $applicant = $this->userWithRoles(['Resident']);
        $this->addResidentProfile($applicant);
        $this->actingAs($applicant)->post(route('marketplace.seller.application.submit'), [
            'business_name' => 'Usaha Warga', 'business_description' => 'Produk olahan lokal',
        ])->assertRedirect();
        $seller = Seller::query()->firstOrFail();
        $this->assertSame('pending', $seller->verification_status);
        $this->actingAs($applicant)->post(route('marketplace.seller.application.submit'), [
            'business_name' => 'Perubahan setelah dikirim', 'business_description' => 'Tidak boleh mengubah antrean aktif',
        ])->assertForbidden();
        $this->assertSame('Usaha Warga', $seller->fresh()->business_name);

        $operator = $this->userWithRoles(['BUMDes Operator']);
        BumdesStaff::query()->create(['user_id' => $operator->id, 'employment_status' => 'active']);
        $this->actingAs($operator)->post(route('marketplace.management.sellers.review', $seller), ['status' => 'approved'])->assertForbidden();
        $this->actingAs($applicant)->post(route('marketplace.management.sellers.review', $seller), ['status' => 'approved'])->assertForbidden();

        $manager = $this->userWithRoles(['BUMDes Manager']);
        BumdesStaff::query()->create(['user_id' => $manager->id, 'employment_status' => 'active']);
        $this->actingAs($manager)->post(route('marketplace.management.sellers.review', $seller), ['status' => 'approved'])->assertRedirect();
        $this->assertSame('approved', $seller->fresh()->verification_status);
        $this->assertTrue($applicant->fresh()->hasRole('Seller'));
        $this->assertDatabaseHas('seller_status_events', ['seller_id' => $seller->id, 'from_status' => 'pending', 'to_status' => 'approved']);
    }

    public function test_checkout_revalidates_availability_and_keeps_cart_when_one_product_is_stale(): void
    {
        $buyer = $this->userWithRoles(['Resident']);
        [, $product] = $this->approvedSellerWithProduct('Stale seller', 'Rengginang', '15000.00', 5);
        $this->actingAs($buyer)->post(route('marketplace.cart.add', $product), ['quantity' => 2])->assertRedirect();
        $product->forceFill(['status' => 'inactive'])->save();

        $this->post(route('marketplace.checkout.store'), [
            'delivery_method' => 'pickup', 'recipient_name' => 'Pembeli', 'recipient_phone' => '08123456789', 'request_key' => Str::random(64),
        ])->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('checkout_groups', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $buyer->cart->id, 'product_id' => $product->id, 'quantity' => 2]);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_cod_stays_pending_until_buyer_confirms_receipt_and_seller_confirms_collection(): void
    {
        $buyer = $this->userWithRoles(['Resident']);
        [$seller, $product] = $this->approvedSellerWithProduct('COD Seller', 'Telur', '30000.00', 3);
        $this->actingAs($buyer)->post(route('marketplace.cart.add', $product), ['quantity' => 1])->assertRedirect();
        $this->post(route('marketplace.checkout.store'), ['delivery_method' => 'pickup', 'recipient_name' => 'Pembeli', 'recipient_phone' => '08123456789', 'request_key' => Str::random(64)])->assertRedirect();
        $order = Order::query()->firstOrFail();
        $payment = $order->payment;

        $this->actingAs($seller)->post(route('marketplace.seller.payments.cod-confirmation', $payment))->assertConflict();
        $this->actingAs($seller)->post(route('marketplace.seller.orders.decision', $order), ['decision' => 'accepted'])->assertRedirect();
        $this->post(route('marketplace.seller.orders.advance', $order), ['status' => 'processing'])->assertRedirect();
        $this->post(route('marketplace.seller.orders.advance', $order), ['status' => 'ready_for_delivery'])->assertRedirect();
        $this->post(route('marketplace.seller.orders.delivery', $order), ['status' => 'delivered'])->assertRedirect();
        $this->assertSame('pending', $payment->fresh()->status);
        $this->actingAs($buyer)->post(route('marketplace.orders.receipt', $order))->assertRedirect();
        $this->assertSame('pending', $payment->fresh()->status);
        $this->actingAs($seller)->post(route('marketplace.seller.payments.cod-confirmation', $payment->fresh()))->assertRedirect();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertDatabaseHas('payment_events', ['payment_id' => $payment->id, 'event_type' => 'cod.collection_confirmed', 'signature_verified' => 0]);
    }

    public function test_verified_gateway_events_validate_amount_and_are_idempotent(): void
    {
        $buyer = $this->userWithRoles(['Resident']);
        [, $product] = $this->approvedSellerWithProduct('Gateway test seller', 'Sambal', '2999.99', 2);
        $this->actingAs($buyer)->post(route('marketplace.cart.add', $product), ['quantity' => 1])->assertRedirect();
        $this->post(route('marketplace.checkout.store'), [
            'delivery_method' => 'pickup', 'recipient_name' => 'Pembeli', 'recipient_phone' => '08123456789', 'request_key' => Str::random(64),
        ])->assertRedirect();
        $payment = Payment::query()->firstOrFail();
        $payment->forceFill(['payment_method' => 'gateway', 'payment_provider' => 'gateway-test', 'provider_reference' => 'gateway-transaction-1'])->save();

        $provider = new class implements MarketplacePaymentProvider
        {
            public ?array $verified = null;

            public function createCashOnDeliveryPayment(Order $order): Payment
            {
                throw new \LogicException('Not used in this webhook test.');
            }

            public function createPendingGatewayPayment(Order $order): Payment
            {
                throw new \LogicException('Not used in this webhook test.');
            }

            public function createOnlinePaymentSession(Payment $payment): Payment
            {
                throw new \LogicException('Not used in this webhook test.');
            }

            public function verifyWebhook(array $payload, array $headers): ?array
            {
                return $this->verified;
            }
        };
        $provider->verified = [
            'provider_reference' => 'gateway-transaction-1', 'order_reference' => $payment->order->order_number, 'amount' => '2999.99', 'currency' => 'IDR',
            'status' => 'paid', 'event_reference' => 'gateway-event-1',
        ];
        $this->app->instance(MarketplacePaymentProvider::class, $provider);
        $processor = app(MarketplaceWebhookProcessor::class);

        $provider->verified['amount'] = '1.00';
        try {
            $processor->process(['untrusted' => 'browser data'], ['x-signature' => 'provider-verified-by-adapter']);
            $this->fail('A mismatched provider amount must be rejected.');
        } catch (ValidationException) {
            $this->assertSame('pending', $payment->fresh()->status);
            $this->assertSame(1, $payment->events()->count());
        }
        $provider->verified['amount'] = '2999.99';
        $this->assertTrue($processor->process(['untrusted' => 'browser data'], ['x-signature' => 'provider-verified-by-adapter']));
        $this->assertFalse($processor->process(['untrusted' => 'replayed'], ['x-signature' => 'provider-verified-by-adapter']));
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame(2, $payment->events()->count());
        $this->assertDatabaseHas('payment_events', ['payment_id' => $payment->id, 'event_reference' => 'gateway-event-1', 'signature_verified' => 1]);

        $provider->verified = null;
        try {
            $processor->process(['status' => 'paid'], ['x-signature' => 'invalid']);
            $this->fail('An unverified provider callback must be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_xendit_multiseller_checkout_is_one_payment_and_orders_are_hidden_until_every_split_is_verified(): void
    {
        config([
            'services.xendit.mode' => 'test',
            'services.xendit.secret_key' => 'xnd_test_fake_secret',
            'services.xendit.webhook_token' => 'test-callback-token',
            'services.xendit.marketplace_seller_payout_percent' => '90.00',
        ]);
        Http::fake([
            'https://api.xendit.co/split_rules' => Http::response(['id' => 'splitru-test-123'], 200),
            'https://api.xendit.co/sessions' => Http::response([
                'payment_session_id' => 'ps-661f87c614802d6c402cd82d',
                'payment_link_url' => 'https://checkout-staging.xendit.co/sessions/ps-661f87c614802d6c402cd82d',
                'expires_at' => now()->addMinutes(30)->toIso8601String(),
            ], 201),
        ]);

        $buyer = $this->userWithRoles(['Resident']);
        [$sellerA, $productA] = $this->approvedSellerWithProduct('Test Xendit seller A', 'Teh lokal', '2999.00', 4);
        [$sellerB, $productB] = $this->approvedSellerWithProduct('Test Xendit seller B', 'Kopi lokal', '1001.00', 4);
        $sellerA->seller->forceFill(['xendit_account_id' => 'sub-account-test-a'])->save();
        $sellerB->seller->forceFill(['xendit_account_id' => 'sub-account-test-b'])->save();
        $this->actingAs($buyer)->post(route('marketplace.cart.add', $productA), ['quantity' => 1])->assertRedirect();
        $this->post(route('marketplace.cart.add', $productB), ['quantity' => 1])->assertRedirect();
        $this->post(route('marketplace.checkout.store'), [
            'delivery_method' => 'pickup',
            'payment_method' => 'xendit',
            'recipient_name' => 'Pembeli uji',
            'recipient_phone' => '08123456789',
            'request_key' => Str::random(64),
        ])->assertRedirect();

        $group = CheckoutGroup::query()->firstOrFail()->load('payment.allocations', 'orders');
        $payment = $group->payment;
        $this->assertSame(2, $group->orders->count());
        $this->assertSame('4000.00', $payment->amount);
        $this->assertSame('401.00', $payment->platform_retained_amount);
        $this->assertSame(['900.00', '2699.00'], $payment->allocations->pluck('amount')->sort()->values()->all());
        $this->assertSame(400000, (int) round(((float) $payment->allocations->sum('amount') + (float) $payment->platform_retained_amount) * 100));
        $this->assertSame(1, Payment::query()->where('checkout_group_id', $group->id)->count());
        $this->assertSame('pending', $payment->status);
        $this->assertSame('xendit', $payment->payment_provider);
        $this->assertSame('awaiting_payment', $group->orders[0]->status);
        $this->actingAs($buyer)->get(route('marketplace.orders.group', $group))
            ->assertOk()
            ->assertSee('Rincian pembagian pembayaran')
            ->assertSee('Rp2.699')
            ->assertSee('Rp900')
            ->assertSee('Rp401');
        $this->actingAs($sellerA)->get(route('marketplace.seller.orders'))->assertOk()->assertDontSee($group->orders[0]->order_number);
        $this->actingAs($sellerA)->get(route('marketplace.orders.show', $group->orders[0]))->assertForbidden();
        $otherBuyer = $this->userWithRoles(['Resident']);
        $this->actingAs($otherBuyer)->post(route('marketplace.orders.group.payment-session', $group))->assertNotFound();

        $this->actingAs($buyer)->post(route('marketplace.orders.group.payment-session', $group))
            ->assertRedirect('https://checkout-staging.xendit.co/sessions/ps-661f87c614802d6c402cd82d');
        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.xendit.co/split_rules'
            && array_sum(array_column($request['routes'], 'flat_amount')) === 3599
            && count($request['routes']) === 2
            && collect($request['routes'])->pluck('destination_account_id')->sort()->values()->all() === ['sub-account-test-a', 'sub-account-test-b']);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.xendit.co/sessions'
            && $request['reference_id'] === $group->checkout_number
            && $request['amount'] === 4000
            && $request['currency'] === 'IDR'
            && $request['items'][0]['category'] === 'Makanan'
            && $request->hasHeader('with-split-rule', 'splitru-test-123')
            && ! isset($request['customer']));
        $this->assertSame('ps-661f87c614802d6c402cd82d', $payment->fresh()->provider_reference);

        $webhook = [
            'event' => 'payment_session.completed',
            'created' => now()->toIso8601String(),
            'data' => [
                'payment_session_id' => 'ps-661f87c614802d6c402cd82d',
                'reference_id' => $group->checkout_number,
                'status' => 'COMPLETED',
                'amount' => 4000,
                'currency' => 'IDR',
            ],
        ];
        $this->postJson('/api/marketplace/xendit/webhook', $webhook, ['x-callback-token' => 'invalid'])->assertForbidden();
        $this->assertSame('pending', $payment->fresh()->status);
        $this->postJson('/api/marketplace/xendit/webhook', $webhook, ['x-callback-token' => 'test-callback-token'])->assertNoContent();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('settlement_pending', $group->fresh()->status);
        $this->actingAs($sellerA)->get(route('marketplace.seller.orders'))->assertOk()->assertDontSee($group->orders[0]->order_number);

        foreach ($payment->fresh()->allocations as $allocation) {
            $splitWebhook = [
                'event' => 'split.payment',
                'data' => [
                    'id' => 'split-payment-'.$allocation->id,
                    'split_rule_id' => 'splitru-test-123',
                    'reference_id' => $allocation->route_reference,
                    'payment_reference_id' => $group->checkout_number,
                    'destination_account_id' => $allocation->destination_account_id,
                    'status' => 'COMPLETED',
                    'amount' => (float) $allocation->amount,
                    'currency' => 'IDR',
                ],
            ];
            $this->postJson('/api/marketplace/xendit/webhook', $splitWebhook, ['x-callback-token' => 'test-callback-token'])->assertNoContent();
        }

        $this->assertSame('pending_seller', $group->fresh()->status);
        $this->assertDatabaseMissing('orders', ['id' => $group->orders[0]->id, 'status' => 'awaiting_payment']);
        $this->actingAs($sellerA)->get(route('marketplace.seller.orders'))->assertOk()->assertSee($group->orders[0]->order_number);
        $this->actingAs($buyer)->get(route('marketplace.orders.group.receipt', $group))
            ->assertOk()
            ->assertSee('Pembagian pembayaran Xendit')
            ->assertSee('Dikonfirmasi Xendit')
            ->assertSee('Saldo platform sebelum biaya Xendit');
        $this->postJson('/api/marketplace/xendit/webhook', $webhook, ['x-callback-token' => 'test-callback-token'])->assertNoContent();
        $this->assertDatabaseCount('payment_events', 4);
    }

    public function test_catalog_cart_application_seller_and_manager_pages_render(): void
    {
        $buyer = $this->userWithRoles(['Resident']);
        $this->addResidentProfile($buyer);
        [$seller, $product] = $this->approvedSellerWithProduct('Warung Warga', 'Kerupuk ikan', '17000.00', 8);
        $this->actingAs($buyer)->get(route('marketplace.index'))->assertOk()->assertSee('Kerupuk ikan');
        $this->get(route('marketplace.products.show', $product))->assertOk()->assertSee('Warung Warga');
        $this->get(route('marketplace.seller.application'))->assertOk()->assertSee('Ajukan sebagai penjual');
        $this->get(route('marketplace.cart'))->assertOk()->assertSee('Keranjang belanja');

        $this->post(route('marketplace.cart.add', $product), ['quantity' => 1])->assertRedirect();
        $this->get(route('marketplace.checkout'))->assertOk()->assertSee('Konfirmasi pesanan');
        $this->actingAs($seller)->get(route('marketplace.seller.products'))->assertOk()->assertSee('Tambah produk');
        $this->get(route('marketplace.seller.products.edit', $product))->assertOk()->assertSee('Ubah produk');

        $manager = $this->userWithRoles(['BUMDes Manager']);
        BumdesStaff::query()->create(['user_id' => $manager->id, 'employment_status' => 'active']);
        Seller::query()->create(['user_id' => $buyer->id, 'business_name' => 'Penjual aktif', 'verification_status' => 'approved']);
        $this->actingAs($manager)->get(route('marketplace.management.sellers'))
            ->assertOk()
            ->assertSee('Pengajuan penjual')
            ->assertSee('Perlu ditinjau')
            ->assertSee('Penjual terdaftar dan status lainnya')
            ->assertSee('Penjual aktif');
        $this->get(route('marketplace.management.categories'))->assertOk()->assertSee('Kategori produk');
        $this->get(route('marketplace.management.products'))->assertOk()->assertSee('Pemeriksaan produk');
    }

    public function test_seller_navigation_changes_with_profile_and_application_eligibility(): void
    {
        $resident = $this->userWithRoles(['Resident']);
        $this->actingAs($resident)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Lengkapi profil warga')
            ->assertDontSee('Daftar sebagai penjual');

        $this->addResidentProfile($resident);
        $this->get(route('dashboard'))->assertOk()->assertSee('Daftar sebagai penjual');

        $seller = Seller::query()->create([
            'user_id' => $resident->getKey(),
            'business_name' => 'Warung milik warga',
            'verification_status' => 'approved',
        ]);
        $sellerRoleId = Role::query()->where('name', 'Seller')->value('id');
        $resident->roles()->attach($sellerRoleId);
        $resident->unsetRelation('roles')->unsetRelation('seller');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Daftar sebagai penjual')
            ->assertDontSee('Status pengajuan penjual')
            ->assertSee('Produk saya')
            ->assertSee('Pesanan toko');

        $this->assertNotNull($seller->fresh());
    }

    public function test_guest_can_browse_marketplace_but_cart_remains_authenticated(): void
    {
        [, $product] = $this->approvedSellerWithProduct('Warung Terbuka', 'Sale pisang', '9000.00', 5);

        $this->get(route('marketplace.index'))->assertOk()->assertSee('Sale pisang')->assertSee('Masuk');
        $this->get(route('marketplace.products.show', $product))->assertOk()->assertSee('Warung Terbuka');
        $this->get(route('marketplace.cart'))->assertRedirect(route('login'));
    }

    public function test_product_upload_rejects_executable_files(): void
    {
        Storage::fake('local');
        [$seller] = $this->approvedSellerWithProduct('Image test seller', 'Existing product', '1000.00', 3);
        $category = ProductCategory::query()->firstOrFail();

        $this->actingAs($seller)->post(route('marketplace.seller.products.store'), [
            'name' => 'Upload test', 'description' => 'Gambar harus tervalidasi.', 'category_id' => $category->id,
            'unit' => 'buah', 'price' => '2000.00', 'stock' => 2,
            'images' => [UploadedFile::fake()->create('payload.php', 2, 'application/x-php')],
        ])->assertSessionHasErrors('images.0');

        $this->assertDatabaseMissing('products', ['name' => 'Upload test']);
        $this->assertSame([], Storage::disk('local')->allFiles('marketplace/products'));
    }

    public function test_product_image_upload_accepts_the_5_mib_boundary_and_rejects_anything_larger(): void
    {
        Storage::fake('local');
        [$seller] = $this->approvedSellerWithProduct('Size limit seller', 'Existing item', '1000.00', 3);
        $category = ProductCategory::query()->firstOrFail();
        $baseData = [
            'description' => 'Test image upload limit.', 'category_id' => $category->id,
            'unit' => 'item', 'price' => '2000.00', 'stock' => 2,
        ];
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pZ8AAAAASUVORK5CYII=', true);
        $this->assertIsString($png);

        $this->actingAs($seller)->post(route('marketplace.seller.products.store'), $baseData + [
            'name' => 'At the limit',
            'images' => [UploadedFile::fake()->createWithContent('limit.png', $png.str_repeat(' ', (5 * 1024 * 1024) - strlen($png)))],
        ])->assertSessionHasNoErrors();

        $this->post(route('marketplace.seller.products.store'), $baseData + [
            'name' => 'Over the limit',
            'images' => [UploadedFile::fake()->createWithContent('too-large.png', $png.str_repeat(' ', (5 * 1024 * 1024 + 1) - strlen($png)))],
        ])->assertSessionHasErrors('images.0');

        $this->assertDatabaseHas('products', ['name' => 'At the limit']);
        $this->assertDatabaseMissing('products', ['name' => 'Over the limit']);
    }

    /** @return array{User, Product} */
    private function approvedSellerWithProduct(string $businessName, string $productName, string $price, int $stock): array
    {
        static $counter = 0;
        $counter++;
        $sellerUser = $this->userWithRoles(['Seller']);
        $seller = Seller::query()->create(['user_id' => $sellerUser->id, 'business_name' => $businessName, 'verification_status' => 'approved']);
        $category = ProductCategory::query()->firstOrCreate(['name' => 'Makanan'], ['slug' => 'makanan', 'is_active' => true]);
        $product = Product::query()->create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => $productName, 'slug' => 'product-'.Str::slug($productName).'-'.$counter,
            'description' => 'Produk lokal untuk pengujian.', 'unit' => 'bungkus',
            'price' => $price, 'stock' => $stock, 'status' => 'active',
        ]);

        return [$sellerUser, $product];
    }

    private function addResidentProfile(User $user): void
    {
        $village = Village::query()->create(['code' => 'V-'.Str::upper(Str::random(6)), 'name' => 'Desa Pasar']);
        $rw = Region::query()->create(['village_id' => $village->id, 'region_type' => 'rw', 'region_code' => 'RW-01', 'name' => 'RW 01']);
        $rt = Region::query()->create(['village_id' => $village->id, 'parent_id' => $rw->id, 'region_type' => 'rt', 'region_code' => 'RT-01', 'name' => 'RT 01']);
        (new Resident)->forceFill(['user_id' => $user->id, 'region_id' => $rt->id, 'full_name_ciphertext' => 'encrypted-test'])->save();
    }

    /** @param list<string> $roles */
    private function userWithRoles(array $roles): User
    {
        $user = User::factory()->active()->create();
        $user->roles()->attach(Role::query()->whereIn('name', $roles)->pluck('id'));

        return $user->load('roles');
    }
}
