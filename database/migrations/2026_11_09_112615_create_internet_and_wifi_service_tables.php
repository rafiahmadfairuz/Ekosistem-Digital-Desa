<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['internet_packages', 'wifi_networks', 'network_devices', 'installation_requests', 'installation_request_events', 'internet_subscriptions', 'subscription_invoices', 'service_requests', 'wifi_access_logs', 'payments', 'payment_events'];
        foreach ($tables as $name) {
            if (Schema::hasTable($name)) {
                throw new RuntimeException("Phase 5 table {$name} already exists; inspect its data and schema before applying this migration.");
            }
        }

        Schema::create('internet_packages', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 14, 2);
            $table->unsignedInteger('bandwidth_download_kbps')->nullable();
            $table->unsignedInteger('bandwidth_upload_kbps')->nullable();
            $table->unsignedSmallInteger('validity_days')->default(30);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
        Schema::create('wifi_networks', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->enum('network_type', ['public_wifi', 'residential', 'office', 'other']);
            $table->string('location', 255);
            $table->string('ssid', 100);
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->json('access_policy')->nullable();
            $table->timestamps();
            $table->index(['network_type', 'status'], 'idx_wifi_networks_type_status');
        });
        Schema::create('network_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('wifi_network_id')->constrained()->restrictOnDelete();
            $table->string('device_name', 120);
            $table->enum('integration_type', ['simulated', 'mikrotik', 'other'])->default('simulated');
            $table->string('device_identifier', 150)->nullable();
            $table->enum('device_status', ['online', 'offline', 'maintenance', 'unknown'])->default('unknown');
            $table->json('configuration')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
        Schema::create('installation_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resident_id')->constrained()->restrictOnDelete();
            $table->foreignId('package_id')->constrained('internet_packages')->restrictOnDelete();
            $table->text('installation_address');
            $table->text('contact_phone')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['submitted', 'reviewing', 'approved', 'rejected', 'scheduled', 'installed', 'cancelled'])->default('submitted');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['status', 'requested_at'], 'idx_installation_requests_status');
            $table->index(['resident_id', 'status'], 'idx_installation_resident_status');
        });
        Schema::create('installation_request_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('installation_request_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['installation_request_id', 'created_at'], 'idx_installation_events_history');
        });
        Schema::create('internet_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resident_id')->constrained()->restrictOnDelete();
            $table->foreignId('package_id')->constrained('internet_packages')->restrictOnDelete();
            $table->foreignId('installation_request_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['pending', 'active', 'suspended', 'expired', 'cancelled'])->default('pending');
            $table->timestamps();
            $table->index(['resident_id', 'status'], 'idx_internet_subscriptions_resident_status');
        });
        Schema::create('subscription_invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_id')->constrained('internet_subscriptions')->restrictOnDelete();
            $table->string('invoice_number', 60)->unique();
            $table->date('billing_period_start');
            $table->date('billing_period_end');
            $table->decimal('amount', 14, 2);
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['unpaid', 'pending', 'paid', 'overdue', 'cancelled'])->default('unpaid');
            $table->timestamps();
            $table->unique(['subscription_id', 'billing_period_start'], 'uq_invoice_subscription_period');
            $table->index(['status', 'due_date'], 'idx_subscription_invoices_due');
        });
        Schema::create('service_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resident_id')->constrained()->restrictOnDelete();
            $table->enum('service_type', ['complaint', 'technical_support', 'billing_question', 'wifi_access', 'other']);
            $table->string('subject', 180);
            $table->text('description');
            $table->enum('status', ['submitted', 'in_progress', 'waiting_resident', 'resolved', 'rejected', 'closed'])->default('submitted');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at'], 'idx_service_requests_status');
            $table->index(['resident_id', 'created_at'], 'idx_service_requests_resident');
        });
        Schema::create('wifi_access_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('wifi_network_id')->constrained()->restrictOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('internet_subscriptions')->restrictOnDelete();
            $table->enum('authentication_status', ['success', 'failed', 'blocked', 'expired']);
            $table->string('session_reference', 150)->nullable();
            $table->char('client_identifier_hash', 64)->nullable();
            $table->binary('ip_address', 16)->nullable();
            $table->timestamp('connected_at')->useCurrent();
            $table->timestamp('disconnected_at')->nullable();
            $table->index(['user_id', 'connected_at'], 'idx_wifi_access_user_time');
            $table->index(['wifi_network_id', 'connected_at'], 'idx_wifi_access_network_time');
        });
        // Invoice payments only; the shared marketplace order target is deferred until Phase 6.
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained('subscription_invoices')->restrictOnDelete();
            $table->enum('payment_method', ['gateway', 'bank_transfer', 'cash', 'other'])->default('bank_transfer');
            $table->string('payment_provider', 80)->nullable();
            $table->string('provider_reference', 180)->nullable();
            $table->decimal('amount', 14, 2);
            $table->enum('status', ['pending', 'authorized', 'paid', 'failed', 'expired', 'cancelled', 'refunded'])->default('pending');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at'], 'idx_payments_status_created');
            $table->index('provider_reference', 'idx_payments_provider_reference');
        });
        Schema::create('payment_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->string('event_type', 100);
            $table->string('event_reference', 180)->nullable()->unique();
            $table->json('payload')->nullable();
            $table->boolean('signature_verified')->default(false);
            $table->timestamp('received_at')->useCurrent();
            $table->index(['payment_id', 'received_at'], 'idx_payment_events_payment_time');
        });
    }

    public function down(): void
    {
        throw new LogicException('Internet service requests, invoices, payments, and access histories are retained; this migration cannot be rolled back destructively.');
    }
};
