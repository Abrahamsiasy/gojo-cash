@props([
    'label' => null,
    'name',
    'options' => [],
    'placeholder' => '',
    'selected' => null,
    'multiple' => false,
    'allowClear' => true,
    'labelClass' => '',
    'class' => '',
])

@php
    $selectId = $attributes->get('id') ?? 'select-'.\Illuminate\Support\Str::random(8);
    $resolvedSelected = old($name, $selected);
    $isMultiple = (bool) $multiple;

    if ($isMultiple) {
        $resolvedSelected = collect($resolvedSelected ?? [])->map(static fn ($value) => (string) $value)->all();
    } elseif (! is_null($resolvedSelected)) {
        $resolvedSelected = (string) $resolvedSelected;
    }
@endphp

@if ($label)
    <label for="{{ $selectId }}" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 {{ $labelClass }}">
        {{ $label }}
    </label>
@endif

<select
    id="{{ $selectId }}"
    name="{{ $name }}{{ $isMultiple ? '[]' : '' }}"
    @if ($isMultiple) multiple @endif
    data-placeholder="{{ $placeholder }}"
    data-allow-clear="{{ $allowClear && ! $isMultiple ? 'true' : 'false' }}"
    {{ $attributes->merge(['class' => 'js-select2-component w-full px-4 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent '.$class]) }}
>
    @if ($placeholder && ! $isMultiple)
        <option value=""></option>
    @endif

    @foreach ($options as $optionValue => $optionLabel)
        @php
            if (is_array($optionLabel)) {
                $value = (string) ($optionLabel['value'] ?? $optionValue);
                $label = $optionLabel['label'] ?? $optionLabel['value'] ?? $optionValue;
            } else {
                $value = (string) $optionValue;
                $label = $optionLabel;
            }

            $isSelected = $isMultiple
                ? in_array($value, $resolvedSelected ?? [], true)
                : ($resolvedSelected !== null && $value === $resolvedSelected);
        @endphp

        <option value="{{ $value }}" @if ($isSelected) selected @endif>
            {{ $label }}
        </option>
    @endforeach
</select>

@error($name)
    <span class="text-red-500 text-sm">{{ $message }}</span>
@enderror

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('vendor/select2/select2.min.css') }}">
        <style>
            .select2-container .select2-selection--single,
            .select2-container .select2-selection--multiple {
                border-radius: 0.75rem;
                padding: 0.375rem 0.75rem;
                border: 1px solid #d1d5db;
                min-height: 42px;
                display: flex;
                align-items: center;
            }

            .dark .select2-container .select2-selection--single,
            .dark .select2-container .select2-selection--multiple {
                background-color: #374151;
                border-color: #4b5563;
                color: #e5e7eb;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: inherit;
                padding-left: 0;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 100%;
            }

            .select2-container--default.select2-container--focus .select2-selection--single,
            .select2-container--default.select2-container--focus .select2-selection--multiple {
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
                border-color: rgba(59, 130, 246, 0.5);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>
        <script>
            function initSelect2Component(context) {
                const $context = context ? $(context) : $(document);

                $context.find('.js-select2-component').each(function () {
                    const $element = $(this);

                    if ($element.data('select2')) {
                        return;
                    }

                    const placeholder = $element.data('placeholder') || '';
                    const allowClearAttr = $element.data('allow-clear');
                    const allowClear = allowClearAttr === true || allowClearAttr === 'true';

                    $element.select2({
                        width: '100%',
                        placeholder,
                        allowClear,
                        dropdownParent: $(document.body),
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', () => initSelect2Component());
            document.addEventListener('turbo:load', () => initSelect2Component());
        </script>
    @endpush
@endonce

