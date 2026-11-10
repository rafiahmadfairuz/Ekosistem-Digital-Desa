<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('regions')) {
            return;
        }

        $invalidRt = DB::table('regions as child')
            ->leftJoin('regions as parent', 'parent.id', '=', 'child.parent_id')
            ->where('child.region_type', 'rt')
            ->where(function ($query): void {
                $query->whereNull('parent.id')
                    ->orWhere('parent.region_type', '!=', 'rw')
                    ->orWhereColumn('child.village_id', '!=', 'parent.village_id');
            })->exists();
        $invalidRw = DB::table('regions')->where('region_type', 'rw')->whereNotNull('parent_id')->exists();

        if ($invalidRt || $invalidRw) {
            throw new RuntimeException('Region hierarchy contains invalid RT/RW parent links; correct them before changing region code uniqueness.');
        }

        if (! Schema::hasColumn('regions', 'code_scope_id')) {
            Schema::table('regions', function (Blueprint $table): void {
                $table->unsignedBigInteger('code_scope_id')->default(0);
            });
        }

        DB::table('regions')->where('region_type', 'rw')->update(['code_scope_id' => 0]);
        DB::table('regions')->where('region_type', 'rt')->update([
            'code_scope_id' => DB::raw('parent_id'),
        ]);

        $indexes = collect(Schema::getIndexes('regions'));
        $hasStandaloneVillageIdIndex = $indexes->contains(
            fn (array $index): bool => ($index['columns'] ?? []) === ['village_id'],
        );

        // The legacy composite unique index may be the only index supporting
        // regions.village_id's foreign key. Replace that support before dropping it.
        if (! $hasStandaloneVillageIdIndex) {
            Schema::table('regions', function (Blueprint $table): void {
                $table->index('village_id', 'idx_regions_village_id');
            });
        }

        $indexNames = collect(Schema::getIndexes('regions'))->pluck('name');
        if ($indexNames->contains('uq_region_village_code')) {
            Schema::table('regions', fn (Blueprint $table) => $table->dropUnique('uq_region_village_code'));
        }
        if (! collect(Schema::getIndexes('regions'))->pluck('name')->contains('uq_region_village_region_scope_code')) {
            Schema::table('regions', function (Blueprint $table): void {
                $table->unique(
                    ['village_id', 'region_type', 'code_scope_id', 'region_code'],
                    'uq_region_village_region_scope_code',
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('regions') || ! Schema::hasColumn('regions', 'code_scope_id')) {
            return;
        }

        $duplicateCodes = DB::table('regions')->select('village_id', 'region_code')
            ->groupBy('village_id', 'region_code')->havingRaw('COUNT(*) > 1')->exists();

        if ($duplicateCodes) {
            throw new LogicException('Region codes now repeat under different parents; the old village-wide unique index cannot be restored safely.');
        }

        Schema::table('regions', function (Blueprint $table): void {
            $table->dropUnique('uq_region_village_region_scope_code');
            $table->dropColumn('code_scope_id');
            $table->unique(['village_id', 'region_code'], 'uq_region_village_code');
        });
    }
};
