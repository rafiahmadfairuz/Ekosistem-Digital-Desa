<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_metadata_drops_secrets_and_identity_values(): void
    {
        $log = app(AuditLogger::class)->record('test.event', 'user', 8, [
            'outcome' => 'success',
            'password' => 'Never store this',
            'national_id' => '1234567890123456',
            'role_ids' => [1, 2],
        ]);

        $this->assertSame(['outcome' => 'success', 'role_ids' => [1, 2]], $log->fresh()->metadata);
        $this->assertDatabaseMissing('audit_logs', ['metadata->password' => 'Never store this']);
    }

    public function test_audit_records_reject_model_updates_and_deletes(): void
    {
        $log = AuditLog::query()->create(['action' => 'test.event', 'entity_type' => 'user', 'entity_id' => 1, 'created_at' => now()]);

        $this->expectException(LogicException::class);
        $log->delete();
    }
}
