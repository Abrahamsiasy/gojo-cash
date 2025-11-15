<x-layouts.app>
    <div class="mb-6 space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    {{ __('Bank Details') }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ __('Review information about this financial bank and manage it here.') }}
                </p>
            </div>

            <a href="{{ route('banks.index') }}"
                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                ← {{ __('Back to banks') }}
            </a>
        </div>

        <div class="">
            <section
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Overview') }}
                </h2>

                <dl class="mt-4 space-y-4 text-sm text-gray-600 dark:text-gray-300">
                    <div class="flex items-center justify-between">
                        <dt>{{ __('Bank Name') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $bank->name }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Status') }}</dt>
                        <dd>
                            @if ($bank->is_active)
                                <span
                                    class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800 dark:bg-green-700/40 dark:text-green-100">
                                    {{ __('Active') }}
                                </span>
                            @else
                                <span
                                    class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800 dark:bg-red-700/40 dark:text-red-100">
                                    {{ __('Inactive') }}
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt>{{ __('Default') }}</dt>
                        <dd>
                            @if ($bank->is_default)
                                <span
                                    class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800 dark:bg-green-700/40 dark:text-green-100">
                                    {{ __('Yes') }}
                                </span>
                            @else
                                <span
                                    class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800 dark:bg-red-700/40 dark:text-red-100">
                                    {{ __('No') }}
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Description') }}
                        </dt>
                        <dd
                            class="whitespace-pre-line bg-gray-50 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ $bank->description ?: __('No description provided.') }}
                        </dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</x-layouts.app>
