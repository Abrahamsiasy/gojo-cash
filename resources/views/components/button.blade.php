@props([
    'type' => 'primary',
    'buttonType' => 'submit',
    'tag' => 'button',
])

@php
    $styleClasses = \Illuminate\Support\Arr::toCssClasses([
        'font-medium py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors flex items-center justify-center cursor-pointer border mr-2 last:mr-0',
        match ($type) {
            'primary' => 'border-blue-600 text-blue-600 bg-transparent hover:bg-blue-50 focus:ring-blue-500 dark:text-blue-400 dark:border-blue-400 dark:hover:bg-blue-900/30',
            'danger' => 'border-transparent bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        },
    ]);
@endphp

<{{ $tag }}
    {{ $attributes->merge(['class' => $styleClasses]) }}
    @if ($tag === 'button')
        type="{{ $buttonType }}"
    @endif
>
    {{ $slot }}
</{{ $tag }}>
