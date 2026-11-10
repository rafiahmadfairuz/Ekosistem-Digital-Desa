<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request, AuditLogger $audit): View
    {
        abort_unless($request->user()->hasRole('Super Admin'), 403);
        $audit->record('audit.logs_viewed', 'audit_log_collection', request: $request);

        $filters = $request->validate([
            'action' => ['nullable', 'string', 'max:120'],
            'entity_type' => ['nullable', 'string', 'max:120'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $logs = AuditLog::query()->with('actor:id,username')->orderByDesc('id')
            ->when($filters['action'] ?? null, fn ($query, $value) => $query->where('action', $value))
            ->when($filters['entity_type'] ?? null, fn ($query, $value) => $query->where('entity_type', $value))
            ->when($filters['date'] ?? null, fn ($query, $value) => $query->whereDate('created_at', $value))
            ->simplePaginate(40)->withQueryString();

        return view('admin.audit-logs.index', compact('logs', 'filters'));
    }
}
