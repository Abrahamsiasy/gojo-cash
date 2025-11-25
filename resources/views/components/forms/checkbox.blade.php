@props([
    'label',
    'name',
    'value' => 1,
    'checked' => false,
    'class' => '',
    'ripple' => true, // enable ripple by default
])

<div class="inline-flex items-center {{ $class }}">
    {{-- Hidden input to ensure 0 is submitted --}}
    <input type="hidden" name="{{ $name }}" value="0">

    {{-- Ripple version --}}
    @if ($ripple)
        <label for="{{ $name }}" class="relative flex cursor-pointer items-center rounded-full p-3"
            data-ripple-dark="true">
            <input id="{{ $name }}" name="{{ $name }}" type="checkbox" value="{{ $value }}"
                {{ $checked || old($name) ? 'checked' : '' }}
                class="peer relative h-5 w-5 cursor-pointer appearance-none rounded border border-slate-300
                       shadow hover:shadow-md transition-all
                       before:absolute before:top-2/4 before:left-2/4 before:block before:h-12 before:w-12
                       before:-translate-y-2/4 before:-translate-x-2/4 before:rounded-full
                       before:bg-slate-400 before:opacity-0 before:transition-opacity
                       checked:border-slate-800 checked:bg-slate-800 checked:before:bg-slate-400
                       hover:before:opacity-10" />

            <span
                class="pointer-events-none absolute top-2/4 left-2/4 -translate-y-2/4 -translate-x-2/4
                         text-white opacity-0 transition-opacity peer-checked:opacity-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"
                    stroke="currentColor" stroke-width="1">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </span>
        </label>
    @else
        {{-- Normal non-ripple version --}}
        <label class="flex items-center cursor-pointer relative" for="{{ $name }}">
            <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}"
                {{ $checked || old($name) ? 'checked' : '' }}
                class="peer h-5 w-5 cursor-pointer transition-all appearance-none rounded shadow hover:shadow-md
                       border border-slate-300 checked:bg-slate-800 checked:border-slate-800" />

            <span
                class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2
                         transform -translate-x-1/2 -translate-y-1/2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"
                    stroke="currentColor" stroke-width="1">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </span>
        </label>
    @endif

    {{-- Label --}}
    <label for="{{ $name }}" class="cursor-pointer ml-2 text-slate-700 dark:text-slate-300 text-sm">
        {{ $label }}
    </label>
</div>

@error($name)
    <span class="text-red-500 text-sm">{{ $message }}</span>
@enderror
