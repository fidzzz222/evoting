@props([
    'type' => 'info',
    'dismissible' => true,
    'title' => null,
])

@php
    $types = [
        'success' => [
            'badge' => 'bg-emerald-500/10 text-emerald-600',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>',
        ],
        'danger' => [
            'badge' => 'bg-rose-500/10 text-rose-600',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        ],
        'error' => [
            'badge' => 'bg-rose-500/10 text-rose-600',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        ],
        'warning' => [
            'badge' => 'bg-amber-500/10 text-amber-600',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        ],
        'info' => [
            'badge' => 'bg-sky-500/10 text-sky-600',
            'icon' => '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
        ],
    ];

    $cfg = $types[$type] ?? $types['info'];
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show" 
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    {{ $attributes->merge(['class' => "rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-xs text-slate-700 flex items-start gap-3 shadow-2xs"]) }}
>
    <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0 {{ $cfg['badge'] }}">
        {!! $cfg['icon'] !!}
    </div>

    <div class="flex-1 min-w-0 pt-0.5">
        @if($title)
            <div class="font-semibold text-slate-800 mb-0.5">{{ $title }}</div>
        @endif
        <div class="leading-relaxed">{{ $slot }}</div>
    </div>

    @if($dismissible)
        <button 
            type="button" 
            @click="show = false" 
            class="shrink-0 p-1 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 transition-colors cursor-pointer"
            aria-label="Tutup"
        >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>
            </svg>
        </button>
    @endif
</div>
