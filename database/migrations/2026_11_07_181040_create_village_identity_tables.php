<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 60)->nullable()->unique('uq_users_username');
            $table->string('phone', 30)->nullable();
            $table->enum('account_status', ['active', 'suspended', 'disabled', 'pending'])->default('pending');
            $table->timestamp('phone_verified_at')->nullable();
            $table->index('account_status', 'idx_users_status');
        });

        DB::table('users')->whereNull('username')->orderBy('id')->eachById(function (object $user): void {
            DB::table('users')->where('id', $user->id)->update(['username' => 'legacy-'.$user->id]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 60)->nullable(false)->change();
            $table->string('email', 255)->nullable()->change();
        });

        Schema::create('villages', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 60)->unique();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('role_id')->constrained()->restrictOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unique(['user_id', 'role_id'], 'uq_user_roles_pair');
        });

        Schema::create('regions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('village_id')->constrained()->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('regions')->restrictOnDelete();
            $table->enum('region_type', ['rw', 'rt']);
            $table->string('region_code', 30);
            $table->string('name', 120);
            $table->string('description', 255)->nullable();
            $table->timestamps();
            $table->unique(['village_id', 'region_code'], 'uq_region_village_code');
            $table->index('parent_id', 'idx_regions_parent');
        });

        Schema::create('residents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('region_id')->constrained()->restrictOnDelete();
            $table->text('national_id_ciphertext')->nullable();
            $table->string('national_id_nonce', 64)->nullable();
            $table->string('national_id_tag', 64)->nullable();
            $table->char('national_id_blind_index', 64)->nullable();
            $table->text('full_name_ciphertext');
            $table->string('full_name_nonce', 64)->nullable();
            $table->string('full_name_tag', 64)->nullable();
            $table->text('phone_ciphertext')->nullable();
            $table->string('phone_nonce', 64)->nullable();
            $table->string('phone_tag', 64)->nullable();
            $table->text('address_ciphertext')->nullable();
            $table->string('address_nonce', 64)->nullable();
            $table->string('address_tag', 64)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'undisclosed'])->nullable();
            $table->timestamps();
            $table->index('region_id', 'idx_residents_region');
            $table->index('national_id_blind_index', 'idx_residents_nik_blind_index');
        });

        Schema::create('region_officers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('region_id')->constrained()->restrictOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('ended_at')->nullable();
            $table->index(['user_id', 'region_id', 'is_active'], 'idx_region_officers_scope');
        });

        Schema::create('bumdes_staff', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('department', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->enum('employment_status', ['active', 'inactive', 'suspended'])->default('active');
            $table->date('joined_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bumdes_staff');
        Schema::dropIfExists('region_officers');
        Schema::dropIfExists('residents');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('villages');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('idx_users_status');
            $table->dropUnique('uq_users_username');
            $table->dropColumn(['username', 'phone', 'account_status', 'phone_verified_at']);
        });
    }
};
