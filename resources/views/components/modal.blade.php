@props([
    'id' => 'default-modal',
    'title' => null,
    'show' => false,
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'confirmColor' => 'red',
])

<div
    x-data="{ open: @js($show) }"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="open = false"
    x-on:open-modal.window="if ($event.detail?.id === '{{ $id }}') { open = true }"
    x-on:close-modal.window="if (! $event.detail?.id || $event.detail.id === '{{ $id }}') { open = false }"
    class="fixed inset-0 z-50 flex items-center justify-center bg-transparent backdrop-blur-sm"
>
    <div
        x-show="open"
        @click.outside="open = false"
        x-transition.scale.duration.150ms
        class="mx-4 w-full max-w-md rounded-2xl border border-gray-200 bg-white/95 p-6 shadow-xl ring-1 ring-gray-100 backdrop-blur-lg dark:border-gray-700 dark:bg-gray-900/95 dark:ring-gray-800"
    >
        <div class="space-y-4">
            @if ($title)
                <div class="flex items-start justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $title }}
                    </h2>
                </div>
            @endif

            <div class="text-sm text-gray-600 dark:text-gray-300">
                {{ $slot }}
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    @click="open = false"
                    class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    {{ $cancelText }}
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-{{ $confirmColor }}-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-{{ $confirmColor }}-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-{{ $confirmColor }}-400"
                    @click="$dispatch('modal-confirm', { id: '{{ $id }}' }); open = false;"
                >
                    {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
</div>
