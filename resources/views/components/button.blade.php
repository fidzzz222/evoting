@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $variants = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 border-transparent shadow-xs',
        'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-400 border-slate-200 shadow-xs',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-600 border-transparent shadow-xs',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-600 border-transparent shadow-xs',
        'outline' => 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50 focus:ring-slate-400 shadow-xs',
        'ghost' => 'bg-transparent border-transparent text-slate-600 hover:bg-slate-100 focus:ring-slate-400',
    ];

    $sizes = [
        'sm' => 'text-xs px-3 py-1.5 rounded-lg gap-1.5 font-bold',
        'md' => 'text-sm px-4 py-2 rounded-xl gap-2 font-bold',
        'lg' => 'text-base px-5 py-2.5 rounded-xl gap-2.5 font-bold',
    ];

    $baseClass = "inline-flex items-center justify-center border transition-colors focus:outline-none focus:ring-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed " . ($variants[$variant] ?? $variants['primary']) . " " . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClass]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClass]) }}>
        {{ $slot }}
    </button>
@endif
