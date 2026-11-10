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
        if (! Schema::hasTable('installation_requests') || ! Schema::hasColumn('installation_requests', 'contact_phone')) {
            throw new RuntimeException('The Phase 5 installation request schema is missing; no changes were made.');
        }
        Schema::table('installation_requests', function (Blueprint $table): void {
            $table->text('contact_phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new LogicException('Encrypted installation contact data must be retained; this migration cannot be rolled back destructively.');
    }
};
