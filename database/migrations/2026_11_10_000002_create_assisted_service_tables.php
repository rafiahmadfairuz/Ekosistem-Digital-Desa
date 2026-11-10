<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assisted_service_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('session_reference', 40)->unique();
            $table->foreignId('resident_id')->constrained()->restrictOnDelete();
            $table->foreignId('operator_id')->constrained('users')->restrictOnDelete();
            $table->string('service_type', 40);
            $table->string('reference_type', 40)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('status', 40)->default('in_progress');
            $table->text('notes')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('identity_verified_at')->nullable();
            $table->timestamp('awaiting_confirmation_at')->nullable();
            $table->timestamp('resident_confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->timestamps();
            $table->index(['operator_id', 'status', 'started_at'], 'idx_assisted_operator_status');
            $table->index(['resident_id', 'started_at'], 'idx_assisted_resident_started');
            $table->index(['service_type', 'status', 'started_at'], 'idx_assisted_category_status');
            $table->index(['reference_type', 'reference_id'], 'idx_assisted_reference');
        });

        Schema::create('assisted_service_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('assisted_service_sessions')->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['session_id', 'created_at'], 'idx_assisted_logs_history');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assisted_service_logs');
        Schema::dropIfExists('assisted_service_sessions');
    }
};
