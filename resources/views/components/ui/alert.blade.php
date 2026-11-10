@props(['type' => 'info', 'role' => 'alert'])
@php
    $styles = [
        'success' => 'border-brand-strong bg-success-bg text-success-ink',
        'error' => 'border-danger bg-danger-bg text-danger-ink',
        'warning' => 'border-amber-700 bg-warning-bg text-warning-ink',
        'info' => 'border-slate-500 bg-slate-100 text-ink',
    ];
@endphp
<div role="{{ $role }}" aria-live="polite" {{ $attributes->class(['border-l-4 px-4 py-3 text-sm leading-6', $styles[$type] ?? $styles['info']]) }}>
    {{ $slot }}
</div>
