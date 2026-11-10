<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table): void {
            $table->timestamp('region_verified_at')->nullable();
            $table->index(['region_id', 'region_verified_at'], 'idx_residents_verified_region');
        });
    }

    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table): void {
            $table->dropIndex('idx_residents_verified_region');
            $table->dropColumn('region_verified_at');
        });
    }
};
