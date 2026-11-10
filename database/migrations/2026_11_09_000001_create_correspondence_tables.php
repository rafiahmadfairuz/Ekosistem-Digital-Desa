<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['letter_types', 'letter_requests', 'letter_request_versions', 'letter_approvals', 'letter_attachments', 'letter_documents'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                throw new RuntimeException('Correspondence table '.$tableName.' already exists; inspect its data and schema before applying the Phase 4 migration.');
            }
        }

        if (Schema::hasTable('notifications')) {
            foreach (['user_id', 'title', 'message', 'reference_type', 'reference_id', 'read_at', 'created_at'] as $column) {
                if (! Schema::hasColumn('notifications', $column)) {
                    throw new RuntimeException('The existing notifications table does not match the project SQL reference; no correspondence tables were created.');
                }
            }
        } else {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->string('title', 180);
                $table->text('message');
                $table->string('reference_type', 100)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['user_id', 'read_at', 'created_at'], 'idx_notifications_user_read');
            });
        }

        Schema::create('letter_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->json('required_fields')->nullable();
            $table->json('required_attachments')->nullable();
            $table->string('template_path', 500)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('letter_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resident_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('letter_type_id')->constrained()->restrictOnDelete();
            $table->string('request_number', 60)->unique();
            $table->enum('submission_channel', ['self_service', 'assisted_bumdes']);
            $table->json('encrypted_data');
            $table->json('letter_type_snapshot');
            $table->enum('status', ['draft', 'submitted', 'rt_review', 'rw_review', 'village_review', 'signature_pending', 'completed', 'revision_required', 'rejected', 'cancelled'])->default('draft');
            $table->timestamp('resident_confirmed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('revision_number')->default(0);
            $table->timestamps();
            $table->index(['resident_id', 'status'], 'idx_letter_requests_resident_status');
            $table->index(['status', 'created_at'], 'idx_letter_requests_status_created');
        });

        Schema::create('letter_request_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('letter_request_id')->constrained()->restrictOnDelete();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->json('encrypted_data');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['letter_request_id', 'version'], 'uq_letter_request_version');
            $table->index(['letter_request_id', 'submitted_at'], 'idx_letter_versions_history');
        });

        Schema::create('letter_approvals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('letter_request_id')->constrained()->restrictOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->string('actor_role', 40);
            $table->enum('approval_level', ['rt', 'rw', 'village_admin', 'village_head']);
            $table->enum('decision', ['approved', 'rejected', 'revision_required', 'signed']);
            $table->text('comments')->nullable();
            $table->boolean('resident_visible')->default(false);
            $table->timestamp('reviewed_at');
            $table->index(['letter_request_id', 'approval_level', 'reviewed_at'], 'idx_letter_approvals_history');
        });

        Schema::create('letter_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('letter_request_id')->constrained()->restrictOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('request_version')->default(1);
            $table->string('attachment_key', 80);
            $table->string('private_file_path', 500);
            $table->string('original_filename', 255)->nullable();
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('file_size');
            $table->char('file_hash', 64);
            $table->timestamp('uploaded_at');
            $table->index(['letter_request_id', 'request_version'], 'idx_letter_attachments_version');
        });

        Schema::create('letter_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('letter_request_id')->constrained()->restrictOnDelete();
            $table->string('document_number', 80)->nullable();
            $table->string('private_file_path', 500);
            $table->char('document_hash', 64);
            $table->unsignedInteger('version');
            $table->foreignId('signed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('signed_at')->nullable();
            $table->enum('signature_type', ['prototype', 'psre'])->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('created_at');
            $table->unique(['letter_request_id', 'version'], 'uq_letter_document_version');
            $table->index('document_hash', 'idx_letter_documents_hash');
        });

    }

    public function down(): void
    {
        throw new LogicException('Correspondence requests and histories are retained; this migration cannot be rolled back destructively.');
    }
};
