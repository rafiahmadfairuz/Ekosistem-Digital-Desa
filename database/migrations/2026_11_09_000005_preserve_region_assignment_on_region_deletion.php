<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('region_officers')) {
            throw new RuntimeException('The region_officers table is required to preserve assignment history when deleting regions.');
        }

        if (! Schema::hasColumn('region_officers', 'former_region_id')) {
            Schema::table('region_officers', function (Blueprint $table): void {
                $table->unsignedBigInteger('former_region_id')->nullable();
                $table->string('former_region_code', 30)->nullable();
                $table->string('former_region_name', 120)->nullable();
                $table->string('former_region_type', 10)->nullable();
                $table->string('former_region_parent_name', 120)->nullable();
                $table->string('former_village_name', 150)->nullable();
                $table->index('former_region_id', 'idx_region_officers_former_region');
            });
        }

        Schema::table('region_officers', function (Blueprint $table): void {
            $table->dropForeign(['region_id']);
            $table->unsignedBigInteger('region_id')->nullable()->change();
        });

        Schema::table('region_officers', function (Blueprint $table): void {
            $table->foreign('region_id')->references('id')->on('regions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('region_officers')->whereNull('region_id')->exists()) {
            throw new RuntimeException('Cannot reverse this migration while deleted-region assignment history exists.');
        }

        Schema::table('region_officers', function (Blueprint $table): void {
            $table->dropForeign(['region_id']);
            $table->unsignedBigInteger('region_id')->nullable(false)->change();
        });

        Schema::table('region_officers', function (Blueprint $table): void {
            $table->foreign('region_id')->references('id')->on('regions')->restrictOnDelete();
            $table->dropIndex('idx_region_officers_former_region');
            $table->dropColumn([
                'former_region_id', 'former_region_code', 'former_region_name',
                'former_region_type', 'former_region_parent_name', 'former_village_name',
            ]);
        });
    }
};
