@props([
    'event',
])

@php
    $status = $event->status;
    $isAfterEnd = $event->isAfterEndTime();
    $isBeforeStart = $event->isBeforeStartTime();
@endphp

@if($status === 'PUBLISHED' && !$isAfterEnd)
    @if($isBeforeStart)
        <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200/80']) }}>
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
            Menunggu Jadwal
        </span>
    @else
        <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/80']) }}>
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Aktif
        </span>
    @endif
@elseif($status === 'CLOSED' || $isAfterEnd)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 bg-slate-50 px-2.5 py-0.5 rounded-full border border-slate-200/80']) }}>
        Ditutup
    </span>
@elseif($status === 'DRAFT')
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200/80']) }}>
        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
        Draft
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200/80']) }}>
        {{ $status }}
    </span>
@endif
