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
        Schema::create('seller_withdrawal_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_withdrawal_id')->constrained()->restrictOnDelete();
            $table->string('event_type', 100);
            $table->string('event_reference', 180)->unique();
            $table->json('payload');
            $table->boolean('signature_verified')->default(false);
            $table->timestamp('received_at')->useCurrent();
            $table->timestamps();
            $table->index(['seller_withdrawal_id', 'received_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new LogicException('Seller withdrawal event history cannot be dropped.');
    }
};
