@props([
    'action',
    'value' => '',
    'placeholder' => '',
    'name' => 'search',
    'clearUrl' => null,
])

@php
    $currentValue = old($name, $value);
    $resetUrl = $clearUrl ?? $action;
@endphp

<form method="GET" action="{{ $action }}" {{ $attributes->merge(['class' => 'flex w-full items-center gap-[5px]']) }}>
    <input
        type="search"
        name="{{ $name }}"
        value="{{ $currentValue }}"
        placeholder="{{ $placeholder }}"
        class="flex-1 sm:flex-none sm:w-64 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 outline-none transition focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
    />

    <x-button class="mr-0 px-3 py-2 text-sm">
        {{ __('Search') }}
    </x-button>

    @if (filled($currentValue))
        <x-button tag="a" href="{{ $resetUrl }}" class="mr-0 px-3 py-2 text-sm">
            {{ __('Reset') }}
        </x-button>
    @endif
</form>