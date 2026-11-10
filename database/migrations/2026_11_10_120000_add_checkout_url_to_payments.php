<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->text('checkout_url')->nullable()->after('provider_reference');
        });
    }

    public function down(): void
    {
        throw new LogicException('Payment checkout references are retained; this migration cannot be rolled back destructively.');
    }
};
