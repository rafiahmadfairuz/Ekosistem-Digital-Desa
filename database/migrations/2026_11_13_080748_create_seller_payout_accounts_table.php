<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seller_payout_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->unique()->constrained()->restrictOnDelete();
            $table->string('bank_name', 100);
            $table->string('bank_swift_code', 20);
            $table->text('account_holder_name');
            $table->text('account_number');
            $table->text('recipient_address');
            $table->string('recipient_type', 20)->default('INDIVIDUAL');
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new LogicException('Seller payout account records contain financial destination history and cannot be dropped.');
    }
};
