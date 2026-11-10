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
            throw new RuntimeException('The region_officers table is required to preserve assignment history when deleting users.');
        }

        if (! Schema::hasColumn('region_officers', 'former_user_id')) {
            Schema::table('region_officers', function (Blueprint $table): void {
                $table->unsignedBigInteger('former_user_id')->nullable()->after('user_id');
                $table->index('former_user_id', 'idx_region_officers_former_user');
            });
        }

        DB::table('region_officers')->whereNull('former_user_id')->update(['former_user_id' => DB::raw('user_id')]);

        Schema::table('region_officers', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('region_officers', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('region_officers')->whereNull('user_id')->exists()) {
            throw new RuntimeException('Cannot reverse this migration while deleted-user assignment history exists.');
        }

        Schema::table('region_officers', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('region_officers', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->dropIndex('idx_region_officers_former_user');
            $table->dropColumn('former_user_id');
        });
    }
};
