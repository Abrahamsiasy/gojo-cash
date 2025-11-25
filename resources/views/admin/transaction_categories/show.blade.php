<x-layouts.app>
    <div class="mb-6 space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    {{ __('Transaction Category Details') }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ __('Review information about this transaction category and manage it here.') }}
                </p>
            </div>

            <a href="{{ route('transaction-categories.index') }}"
                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                ← {{ __('Back to categories') }}
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <!-- Overview Section -->
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Overview') }}
                </h2>

                <dl class="mt-4 space-y-4 text-sm text-gray-600 dark:text-gray-300">
                    <div class="flex items-center justify-between">
                        <dt>{{ __('Category Name') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $transactionCategory->name }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Company') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            @if ($transactionCategory->company)
                                <a href="{{ route('companies.show', $transactionCategory->company) }}"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $transactionCategory->company->name }}
                                </a>
                            @else
                                {{ __('—') }}
                            @endif
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Type') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ ucfirst($transactionCategory->type) }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Default') }}</dt>
                        <dd>
                            @if ($transactionCategory->is_default)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800 dark:bg-green-700/40 dark:text-green-100">
                                    {{ __('Yes') }}
                                </span>
                            @else
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800 dark:bg-gray-700/40 dark:text-gray-100">
                                    {{ __('No') }}
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Description') }}
                        </dt>
                        <dd class="whitespace-pre-line rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ $transactionCategory->description ?: __('No description provided.') }}
                        </dd>
                    </div>
                </dl>
            </section>

            <!-- Metrics & Actions Section -->
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Category Info') }}
                </h2>

                <dl class="mt-4 space-y-4 text-sm text-gray-600 dark:text-gray-300">
                    <div class="flex items-center justify-between">
                        <dt>{{ __('Created At') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ optional($transactionCategory->created_at)?->format('F j, Y, g:i A') ?? __('—') }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Last Updated') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ optional($transactionCategory->updated_at)?->diffForHumans() ?? __('—') }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <form
                        method="POST"
                        action="{{ route('transaction-categories.destroy', $transactionCategory) }}"
                        x-data
                        x-on:modal-confirm.window="if ($event.detail?.id === 'delete-category-{{ $transactionCategory->id }}') { $el.submit() }"
                    >
                        @csrf
                        @method('DELETE')

                        <x-button
                            type="danger"
                            buttonType="button"
                            class="w-full sm:w-auto"
                            @click="$dispatch('open-modal', { id: 'delete-category-{{ $transactionCategory->id }}' })"
                        >
                            {{ __('Delete Category') }}
                        </x-button>

                        <x-modal
                            id="delete-category-{{ $transactionCategory->id }}"
                            title="{{ __('Delete Category') }}"
                            confirmText="{{ __('Delete') }}"
                            cancelText="{{ __('Cancel') }}"
                            confirmColor="red"
                        >
                            {{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $transactionCategory->name]) }}
                        </x-modal>
                    </form>

                    <x-button
                        tag="a"
                        href="{{ route('transaction-categories.edit', $transactionCategory) }}"
                        class="w-full sm:w-auto"
                    >
                        {{ __('Edit Category') }}
                    </x-button>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
