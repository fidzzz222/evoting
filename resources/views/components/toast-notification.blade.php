@props([
    'type' => 'success',
    'show' => null,
    'timeout' => null,
    'fixed' => true,
    'dismissible' => false,
])

@php
    $icons = [
        'success' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>',
        'error' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        'warning' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        'info' => '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
    ];

    $badgeColors = [
        'success' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
        'error' => 'bg-rose-500/15 text-rose-400 border-rose-500/20',
        'warning' => 'bg-amber-500/15 text-amber-400 border-amber-500/20',
        'info' => 'bg-sky-500/15 text-sky-400 border-sky-500/20',
    ];

    $icon = $icons[$type] ?? $icons['success'];
    $badge = $badgeColors[$type] ?? $badgeColors['success'];
    $role = $type === 'error' ? 'alert' : 'status';
@endphp

<div 
    @if(!$show) x-data="{ show: true }" x-show="show" @else x-show="{{ $show }}" @endif
    @if($timeout) x-init="setTimeout(() => {{ $show ? "$show = ''" : 'show = false' }}, {{ $timeout }})" @endif
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-1 sm:translate-y-0 sm:scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 sm:scale-100"
    x-transition:leave-end="opacity-0 sm:scale-95"
    {{ $attributes->merge(['class' => ($fixed ? 'fixed top-4 right-4 left-4 sm:left-auto sm:right-6 sm:top-6 z-50 pointer-events-none max-w-sm w-auto' : '')]) }}
    @if($show) style="display: none;" @endif
    role="{{ $role }}"
>
    <div class="pointer-events-auto flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-100 shadow-xl shadow-slate-950/20 text-xs">
        <span class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 border {{ $badge }}">
            {!! $icon !!}
        </span>
        @if($show)
            <span class="flex-1 font-medium text-slate-200 leading-relaxed" x-text="{{ $show }}"></span>
        @else
            <div class="flex-1 font-medium text-slate-200 leading-relaxed">{{ $slot }}</div>
        @endif
        @if($dismissible)
            <button @click="{{ $show ? "$show = ''" : 'show = false' }}" type="button" class="text-slate-400 hover:text-white p-1 rounded-md transition-colors cursor-pointer shrink-0 ml-auto" aria-label="Tutup notifikasi">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</div>
