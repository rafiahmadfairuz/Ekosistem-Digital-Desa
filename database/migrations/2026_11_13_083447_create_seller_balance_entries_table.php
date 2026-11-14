<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_balance_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('seller_withdrawal_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('entry_type', 40);
            $table->string('reference_id', 120)->unique();
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('IDR');
            $table->string('description', 180);
            $table->timestamps();
            $table->unique(['order_id', 'entry_type']);
            $table->index(['seller_id', 'created_at']);
        });
    }

    public function down(): void
    {
        throw new LogicException('Seller balance ledger entries are financial transaction history and cannot be dropped.');
    }
};
