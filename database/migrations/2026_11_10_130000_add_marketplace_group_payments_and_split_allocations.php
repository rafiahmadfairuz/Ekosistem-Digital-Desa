<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('checkout_group_id')->nullable()->after('order_id')->constrained()->restrictOnDelete();
            $table->string('xendit_split_rule_id', 120)->nullable()->after('checkout_url');
            $table->decimal('platform_retained_amount', 14, 2)->nullable()->after('amount');
            $table->index(['checkout_group_id', 'status'], 'idx_payments_checkout_group_status');
            $table->unique('checkout_group_id', 'uq_payments_checkout_group');
        });

        Schema::table('sellers', function (Blueprint $table): void {
            $table->string('xendit_account_id', 80)->nullable()->unique('uq_sellers_xendit_account_id')->after('verification_status');
        });

        Schema::create('payment_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->string('destination_account_id', 80);
            $table->string('route_reference', 100)->unique();
            $table->decimal('amount', 14, 2);
            $table->string('status', 20)->default('pending')->index();
            $table->string('provider_split_reference', 120)->nullable()->unique();
            $table->string('failure_code', 100)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['payment_id', 'order_id'], 'uq_payment_allocation_order');
            $table->index(['seller_id', 'status']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE payments DROP CONSTRAINT chk_payments_order_or_invoice');
            DB::statement('ALTER TABLE payments ADD CONSTRAINT chk_payments_order_invoice_or_group CHECK ((order_id IS NOT NULL AND invoice_id IS NULL AND checkout_group_id IS NULL) OR (order_id IS NULL AND invoice_id IS NOT NULL AND checkout_group_id IS NULL) OR (order_id IS NULL AND invoice_id IS NULL AND checkout_group_id IS NOT NULL))');
            DB::statement("ALTER TABLE payment_allocations ADD CONSTRAINT chk_payment_allocations_positive_amount CHECK (amount > 0)");
            DB::statement("ALTER TABLE payment_allocations ADD CONSTRAINT chk_payment_allocations_status CHECK (status IN ('pending','completed','failed'))");
            DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payments_platform_retained_nonnegative CHECK (platform_retained_amount IS NULL OR platform_retained_amount >= 0)");
        }
    }

    public function down(): void
    {
        throw new LogicException('Marketplace payment and seller settlement history is retained; this migration cannot be rolled back destructively.');
    }
};
