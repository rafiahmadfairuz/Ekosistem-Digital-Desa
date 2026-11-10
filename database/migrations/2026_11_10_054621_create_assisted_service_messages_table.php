<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assisted_service_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('assisted_service_sessions')->restrictOnDelete();
            $table->foreignId('sender_id')->constrained('users')->restrictOnDelete();
            $table->text('body');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['session_id', 'created_at', 'id'], 'idx_assisted_messages_history');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assisted_service_messages');
    }
};
