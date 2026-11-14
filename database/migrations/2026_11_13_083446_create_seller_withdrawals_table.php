<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->foreignId('seller_payout_account_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('request_key', 80)->unique();
            $table->string('reference_id', 100)->unique();
            $table->string('xendit_payout_id', 120)->nullable()->unique();
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('IDR');
            $table->string('status', 32)->default('requested')->index();
            $table->string('failure_code', 100)->nullable();
            $table->text('account_snapshot');
            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['seller_id', 'status']);
        });
    }

    public function down(): void
    {
        throw new LogicException('Seller withdrawal records are financial transaction history and cannot be dropped.');
    }
};
