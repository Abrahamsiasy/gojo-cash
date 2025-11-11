@php
    $colors = [
        'info' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'ring' => 'focus:ring-blue-400', 'hover' => 'hover:bg-blue-200', 'icon' => 'text-blue-500'],
        'danger' => ['bg' => 'bg-red-50', 'text' => 'text-red-800', 'ring' => 'focus:ring-red-400', 'hover' => 'hover:bg-red-200', 'icon' => 'text-red-500'],
        'success' => ['bg' => 'bg-green-50', 'text' => 'text-green-800', 'ring' => 'focus:ring-green-400', 'hover' => 'hover:bg-green-200', 'icon' => 'text-green-500'],
        'warning' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-800', 'ring' => 'focus:ring-yellow-400', 'hover' => 'hover:bg-yellow-200', 'icon' => 'text-yellow-500'],
        'dark' => ['bg' => 'bg-gray-50 dark:bg-gray-800', 'text' => 'text-gray-800 dark:text-gray-300', 'ring' => 'focus:ring-gray-400', 'hover' => 'hover:bg-gray-200 dark:hover:bg-gray-700', 'icon' => 'text-gray-500 dark:text-gray-300'],
    ];

    $color = $colors[$type] ?? $colors['info'];
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 5000)" {{-- Auto-hide after 5 seconds --}}
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform -translate-y-2"
    {{ $attributes->merge(['class' => "w-full flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4 p-4 mb-4 rounded-lg {$color['bg']} {$color['text']} shadow"]) }}
    role="alert"
>
    <svg class="h-5 w-5 shrink-0 {{ $color['icon'] }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
    </svg>

    <div class="text-sm font-medium sm:flex-1">{!! $message !!}</div>

    <button
        @click="show = false"
        type="button"
        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $color['bg'] }} {{ $color['icon'] }} {{ $color['ring'] }} {{ $color['hover'] }} sm:ms-auto"
        aria-label="Close"
    >
        <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
        </svg>
    </button>
</div>
