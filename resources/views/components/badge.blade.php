@props([
    'variant' => 'neutral',
    'dot' => false,
    'size' => 'md',
])

@php
    $variants = [
        'primary' => 'bg-blue-50 text-blue-700 border-blue-200/80',
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200/80',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200/80',
        'info' => 'bg-sky-50 text-sky-700 border-sky-200/80',
        'neutral' => 'bg-slate-100 text-slate-700 border-slate-200',
        'purple' => 'bg-slate-100 text-slate-700 border-slate-200',
    ];

    $dotColors = [
        'primary' => 'bg-blue-600',
        'success' => 'bg-emerald-500',
        'danger' => 'bg-rose-500',
        'warning' => 'bg-amber-500',
        'info' => 'bg-sky-500',
        'neutral' => 'bg-slate-400',
        'purple' => 'bg-slate-500',
    ];

    $sizes = [
        'sm' => 'text-[11px] px-2 py-0.5',
        'md' => 'text-xs px-2.5 py-1',
        'lg' => 'text-sm px-3 py-1.5',
    ];

    $badgeClass = ($variants[$variant] ?? $variants['neutral']) . ' ' . ($sizes[$size] ?? $sizes['md']);
    $dotClass = $dotColors[$variant] ?? $dotColors['neutral'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 font-semibold rounded-full border {$badgeClass}"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
    @endif
    {{ $slot }}
</span>
