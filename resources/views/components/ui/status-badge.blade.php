@props(['status', 'tone' => 'neutral'])
@php
    $tones = [
        'neutral' => 'bg-slate-100 text-ink',
        'success' => 'bg-success-bg text-success-ink',
        'warning' => 'bg-warning-bg text-warning-ink',
        'danger' => 'bg-danger-bg text-danger-ink',
    ];
@endphp
<span {{ $attributes->class(['inline-flex min-h-6 items-center rounded-full px-2 text-xs font-medium', $tones[$tone] ?? $tones['neutral']]) }}>
    {{ $status }}
</span>
