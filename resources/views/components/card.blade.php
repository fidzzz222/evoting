@props([
    'title' => null,
    'subtitle' => null,
    'action' => null,
    'footer' => null,
    'padding' => true,
    'noPadding' => false,
])

@php
    $hasPadding = $padding && !$noPadding;
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden']) }}>
    @if($title || $action)
        <div class="px-6 py-4.5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 bg-slate-50">
            <div>
                @if($title)
                    <h3 class="text-base font-bold text-slate-800 tracking-tight">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ $subtitle }}</p>
                @endif
            </div>
            @if($action)
                <div class="flex items-center gap-2">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $hasPadding ? 'p-6' : '' }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 text-xs text-slate-500 flex items-center justify-between">
            {{ $footer }}
        </div>
    @endif
</div>
