<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assisted_service_sessions', function (Blueprint $table): void {
            $table->foreignId('operator_id')->nullable()->change();
            $table->timestamp('started_at')->nullable()->change();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason', 500)->nullable();
        });

        DB::table('assisted_service_sessions')->whereNull('requested_at')->update([
            'requested_at' => DB::raw('started_at'),
        ]);
    }

    public function down(): void
    {
        if (DB::table('assisted_service_sessions')->whereNull('operator_id')->exists()) {
            throw new RuntimeException('Cannot roll back assistance requests while unassigned requests exist.');
        }

        Schema::table('assisted_service_sessions', function (Blueprint $table): void {
            $table->dropColumn(['requested_at', 'accepted_at', 'rejected_at', 'rejection_reason']);
            $table->foreignId('operator_id')->nullable(false)->change();
        });
    }
};
