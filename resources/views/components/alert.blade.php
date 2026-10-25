@props([
    'type' => 'info',
    'dismissible' => true,
    'title' => null,
])

@php
    $types = [
        'success' => [
            'bg' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
            'icon' => '<svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'close' => 'text-emerald-500 hover:text-emerald-700 hover:bg-emerald-100',
        ],
        'danger' => [
            'bg' => 'bg-rose-50 border-rose-200 text-rose-800',
            'icon' => '<svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'close' => 'text-rose-500 hover:text-rose-700 hover:bg-rose-100',
        ],
        'error' => [
            'bg' => 'bg-rose-50 border-rose-200 text-rose-800',
            'icon' => '<svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'close' => 'text-rose-500 hover:text-rose-700 hover:bg-rose-100',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 border-amber-200 text-amber-800',
            'icon' => '<svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            'close' => 'text-amber-500 hover:text-amber-700 hover:bg-amber-100',
        ],
        'info' => [
            'bg' => 'bg-sky-50 border-sky-200 text-sky-800',
            'icon' => '<svg class="w-5 h-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'close' => 'text-sky-500 hover:text-sky-700 hover:bg-sky-100',
        ],
    ];

    $cfg = $types[$type] ?? $types['info'];
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show" 
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    {{ $attributes->merge(['class' => "rounded-2xl border p-4 shadow-2xs flex items-start gap-3.5 {$cfg['bg']}"]) }}
>
    <div class="shrink-0 pt-0.5">
        {!! $cfg['icon'] !!}
    </div>

    <div class="flex-1 text-sm leading-relaxed">
        @if($title)
            <div class="font-bold mb-1">{{ $title }}</div>
        @endif
        {{ $slot }}
    </div>

    @if($dismissible)
        <button 
            type="button" 
            @click="show = false" 
            class="shrink-0 p-1 rounded-lg transition-colors cursor-pointer {{ $cfg['close'] }}"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>
