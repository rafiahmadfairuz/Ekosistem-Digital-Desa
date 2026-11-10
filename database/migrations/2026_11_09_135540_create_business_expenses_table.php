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
        Schema::create('business_expenses', function (Blueprint $table): void {
            $table->id();
            $table->string('category', 40);
            $table->string('description', 1000);
            $table->decimal('amount', 14, 2)->unsigned();
            $table->date('expense_date');
            $table->enum('status', ['recorded', 'voided'])->default('recorded');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->string('void_reason', 500)->nullable();
            $table->timestamps();
            $table->index(['status', 'expense_date'], 'idx_business_expenses_status_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_expenses');
    }
};
