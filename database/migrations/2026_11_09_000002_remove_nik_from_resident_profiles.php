<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('residents')) {
            return;
        }

        if (Schema::hasColumn('residents', 'national_id_blind_index')) {
            Schema::table('residents', function (Blueprint $table): void {
                $table->dropIndex('idx_residents_nik_blind_index');
            });
        }

        $columns = array_values(array_filter(
            ['national_id_ciphertext', 'national_id_nonce', 'national_id_tag', 'national_id_blind_index'],
            fn (string $column): bool => Schema::hasColumn('residents', $column),
        ));

        if ($columns !== []) {
            Schema::table('residents', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }

    public function down(): void
    {
        throw new LogicException('Resident NIK columns were removed to comply with the data-retention policy and cannot be restored by rollback.');
    }
};
