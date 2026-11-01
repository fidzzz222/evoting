@props([
    'title',
    'value',
    'subtitle' => null,
    'helper' => null,
    'icon' => null,
    'color' => 'blue',
    'trend' => null,
    'xValue' => null,
])

@php
    $subText = $subtitle ?? $helper;
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs transition-colors relative overflow-hidden flex flex-col justify-between']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 block">{{ $title }}</span>
            <div class="text-2xl sm:text-3xl font-bold tabular-nums text-slate-900 tracking-tight" @if($xValue) x-text="{{ $xValue }}" @endif>
                {{ $value }}
            </div>
        </div>

        @if($icon)
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-slate-600 bg-slate-100/90 border border-slate-200/80 shadow-2xs">
                {!! $icon !!}
            </div>
        @elseif(isset($slot) && $slot->isNotEmpty())
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-slate-600 bg-slate-100/90 border border-slate-200/80 shadow-2xs">
                {{ $slot }}
            </div>
        @endif
    </div>

    @if($subText)
        <div class="pt-3 mt-3 border-t border-slate-100 text-xs text-slate-500 font-medium flex items-center gap-1.5">
            @if($color === 'emerald')
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
            @elseif($color === 'amber')
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
            @endif
            @if($trend)
                <span class="font-semibold text-emerald-600">{{ $trend }}</span>
            @endif
            <span>{{ $subText }}</span>
        </div>
    @endif
</div>
