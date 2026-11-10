@props(['variant' => 'primary', 'type' => 'button'])
@php
    $variants = [
        'primary' => 'bg-brand-strong font-semibold text-white hover:bg-emerald-900',
        'secondary' => 'border border-slate-500 font-medium hover:bg-slate-100',
        'danger' => 'bg-danger font-semibold text-white hover:bg-red-800',
        'plain' => 'font-medium underline underline-offset-4 hover:text-emerald-900',
    ];
@endphp
<button type="{{ $type }}" {{ $attributes->class(['inline-flex min-h-11 items-center justify-center gap-2 rounded-control px-4 text-sm focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60', $variants[$variant] ?? $variants['primary']]) }}>
    {{ $slot }}
</button>
