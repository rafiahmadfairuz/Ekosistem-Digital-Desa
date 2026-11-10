<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /** @param array<string, mixed> $metadata */
    public function record(
        string $action,
        string $entityType,
        ?int $entityId = null,
        array $metadata = [],
        ?Request $request = null,
    ): AuditLog {
        $request ??= request();
        $ip = $request->ip();
        $binaryIp = is_string($ip) ? @inet_pton($ip) : false;

        return AuditLog::query()->create([
            'user_id' => Auth::id(),
            'action' => mb_substr($action, 0, 120),
            'entity_type' => mb_substr($entityType, 0, 120),
            'entity_id' => $entityId,
            'metadata' => $this->safeMetadata($metadata),
            'ip_address' => $binaryIp === false ? null : $binaryIp,
            'user_agent' => $request->userAgent() === null ? null : mb_substr($request->userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }

    /** @param array<string, mixed> $metadata
     * @return array<string, scalar|array<int, scalar>|null>
     */
    private function safeMetadata(array $metadata): array
    {
        $safe = [];
        foreach ($metadata as $key => $value) {
            if (preg_match('/password|token|secret|national.?id|nik|phone|email|address|payload|body|cipher|nonce|tag/i', (string) $key)) {
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $safe[(string) $key] = $value;

                continue;
            }

            if (is_array($value) && array_is_list($value) && count(array_filter($value, fn ($item): bool => is_scalar($item))) === count($value)) {
                $safe[(string) $key] = $value;
            }
        }

        return $safe;
    }
}
