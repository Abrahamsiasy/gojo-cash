@props([
    'label' => null,
    'name',
    'placeholder' => '',
    'value' => old($name),
    'rows' => 4,
    'labelClass' => '',
    'class' => '',
])

@if ($label)
    <label for="{{ $name }}" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 {{ $labelClass }}">
        {{ $label }}
    </label>
@endif

<textarea
    id="{{ $name }}"
    name="{{ $name }}"
    rows="{{ $rows }}"
    placeholder="{{ $placeholder }}"
    {{ $attributes->merge(['class' => 'w-full px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent '.$class]) }}
>{{ old($name, $value) }}</textarea>

@error($name)
    <span class="text-red-500 text-sm">{{ $message }}</span>
@enderror

