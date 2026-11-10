<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('checkout_groups')) {
            throw new LogicException('The Phase 6 checkout_groups table is missing. Apply the marketplace migration first.');
        }

        if (! Schema::hasColumn('checkout_groups', 'buyer_archived_at')) {
            Schema::table('checkout_groups', function (Blueprint $table): void {
                $table->timestamp('buyer_archived_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        throw new LogicException('Buyer archive state is retained as part of marketplace history; this migration is forward-only.');
    }
};
