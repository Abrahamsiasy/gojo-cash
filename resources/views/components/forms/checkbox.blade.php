@props([
    'label',
    'name',
    'value' => 1,
    'checked' => false,
    'class' => '',
])

<label for="{{ $name }}" class="flex items-center text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
    {{-- Hidden field ensures 0 is submitted when unchecked --}}
    <input type="hidden" name="{{ $name }}" value="0">

    <input
        type="checkbox"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ $value }}"
        {{ $checked || old($name) ? 'checked' : '' }}
        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-2 {{ $class }}"
    >

    {{ $label }}
</label>

@error($name)
    <span class="text-red-500 text-sm">{{ $message }}</span>
@enderror
