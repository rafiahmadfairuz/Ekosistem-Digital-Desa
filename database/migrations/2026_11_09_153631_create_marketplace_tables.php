<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments') || ! Schema::hasColumn('payments', 'invoice_id') || ! Schema::hasColumn('payments', 'amount')) {
            throw new LogicException('The existing Phase 5 payments table is missing required invoice_id or amount columns; no marketplace schema was created.');
        }
        if (DB::table('payments')->whereNull('invoice_id')->orWhere('amount', '<=', 0)->exists()) {
            throw new LogicException('Existing payment rows contain an empty invoice reference or non-positive amount. Review those rows before applying the Phase 6 payments extension.');
        }

        $tables = [
            'sellers', 'seller_status_events', 'seller_documents', 'product_categories',
            'products', 'product_images', 'product_moderation_events', 'carts', 'cart_items',
            'checkout_groups', 'orders', 'order_items', 'order_status_events', 'deliveries',
            'delivery_events',
        ];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                throw new LogicException("Marketplace table {$tableName} already exists; inspect its schema and data before continuing.");
            }
        }

        Schema::create('sellers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('business_name', 160);
            $table->text('business_description')->nullable();
            $table->text('business_address')->nullable();
            $table->text('contact_phone')->nullable();
            $table->string('verification_status', 30)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('seller_status_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['seller_id', 'created_at']);
        });
        Schema::create('seller_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('document_type', 60);
            $table->string('private_file_path', 500);
            $table->string('original_filename', 255)->nullable();
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('file_size');
            $table->char('file_hash', 64);
            $table->timestamp('uploaded_at')->useCurrent();
            $table->index(['seller_id', 'uploaded_at']);
        });
        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained('product_categories')->restrictOnDelete();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->text('description');
            $table->string('unit', 40)->default('item');
            $table->decimal('price', 14, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->string('status', 30)->default('draft')->index();
            $table->text('moderation_note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['seller_id', 'status', 'created_at']);
            $table->index(['category_id', 'status', 'price']);
            $table->index(['status', 'created_at']);
        });
        Schema::create('product_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('private_file_path', 500);
            $table->string('alt_text', 180);
            $table->string('mime_type', 80);
            $table->unsignedInteger('file_size');
            $table->char('file_hash', 64);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'sort_order']);
        });
        Schema::create('product_moderation_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['product_id', 'created_at']);
        });
        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('open')->index();
            $table->timestamps();
        });
        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cart_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(['cart_id', 'product_id'], 'uq_cart_product');
            $table->index(['product_id', 'cart_id']);
        });
        Schema::create('checkout_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->string('checkout_number', 60)->unique();
            $table->string('request_key', 64);
            $table->decimal('grand_total', 14, 2);
            $table->string('status', 30)->default('pending')->index();
            $table->timestamps();
            $table->index(['buyer_id', 'created_at']);
            $table->unique(['buyer_id', 'request_key'], 'uq_checkout_buyer_request_key');
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('checkout_group_id')->constrained()->restrictOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->string('order_number', 60)->unique();
            $table->decimal('total_amount', 14, 2);
            $table->string('payment_method', 30);
            $table->string('status', 30)->default('pending_seller')->index();
            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['seller_id', 'status', 'created_at']);
            $table->index(['buyer_id', 'created_at']);
            $table->index(['checkout_group_id', 'seller_id']);
        });
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name_snapshot', 180);
            $table->decimal('unit_price', 14, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 14, 2);
            $table->json('attributes_snapshot')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['order_id', 'product_id']);
        });
        Schema::create('order_status_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['order_id', 'created_at']);
        });
        Schema::create('deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->string('delivery_method', 30);
            $table->text('delivery_address')->nullable();
            $table->text('recipient_name')->nullable();
            $table->text('recipient_phone')->nullable();
            $table->string('delivery_reference', 100)->nullable();
            $table->string('delivery_status', 30)->default('preparing')->index();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('buyer_confirmed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('delivery_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['delivery_id', 'created_at']);
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('order_id')->nullable()->after('id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('invoice_id')->nullable()->change();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE payments ADD CONSTRAINT chk_payments_order_or_invoice CHECK ((order_id IS NOT NULL AND invoice_id IS NULL) OR (order_id IS NULL AND invoice_id IS NOT NULL))');
            DB::statement('ALTER TABLE payments ADD CONSTRAINT chk_payments_positive_amount CHECK (amount > 0)');
            DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_positive_price CHECK (price > 0)');
            DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_nonnegative_stock CHECK (stock >= 0)');
            DB::statement('ALTER TABLE cart_items ADD CONSTRAINT chk_cart_items_positive_quantity CHECK (quantity > 0)');
            DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_positive_quantity CHECK (quantity > 0)');
            DB::statement("ALTER TABLE sellers ADD CONSTRAINT chk_sellers_status CHECK (verification_status IN ('pending','under_review','approved','rejected','suspended','inactive'))");
            DB::statement("ALTER TABLE products ADD CONSTRAINT chk_products_status CHECK (status IN ('draft','pending_review','active','rejected','inactive','out_of_stock','archived'))");
            DB::statement("ALTER TABLE orders ADD CONSTRAINT chk_orders_status CHECK (status IN ('pending_seller','accepted','rejected','processing','ready_for_delivery','in_delivery','delivered','delivery_failed','completed','cancelled','awaiting_payment'))");
            DB::statement("ALTER TABLE deliveries ADD CONSTRAINT chk_deliveries_status CHECK (delivery_status IN ('preparing','ready','in_delivery','delivered','failed','confirmed'))");
            DB::statement("ALTER TABLE deliveries ADD CONSTRAINT chk_deliveries_method CHECK (delivery_method IN ('delivery','pickup'))");
        }
    }

    public function down(): void
    {
        throw new LogicException('Marketplace orders and financial history are retained; this migration cannot be rolled back destructively.');
    }
};
