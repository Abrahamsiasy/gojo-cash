{{-- <x-layouts.app>
    <div class="">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Companies') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('View componys linked to your expense management system.') }}
        </p>
    </div>

</x-layouts.app> --}}

<x-layouts.app>
    <div class="mb-6 space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    {{ __('Account Details') }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ __('Review information about this financial account and manage it here.') }}
                </p>
            </div>

            <a href="{{ route('accounts.index') }}"
                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                ← {{ __('Back to accounts') }}
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Overview') }}
                </h2>

                <dl class="mt-4 space-y-4 text-sm text-gray-600 dark:text-gray-300">
                    <div class="flex items-center justify-between">
                        <dt>{{ __('Account Name') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $account->name }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Company') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            @if ($account->company)
                                <a href="{{ route('companies.show', $account->company) }}"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $account->company->name }}
                                </a>
                            @else
                                {{ __('—') }}
                            @endif
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Account Number') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $account->account_number ?: __('—') }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Account Type') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ \Illuminate\Support\Str::headline($account->account_type?->value ?? '—') }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Bank Name') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $account->bank_name ?: __('—') }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Status') }}</dt>
                        <dd>
                            @if ($account->is_active)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800 dark:bg-green-700/40 dark:text-green-100">
                                    {{ __('Active') }}
                                </span>
                            @else
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800 dark:bg-red-700/40 dark:text-red-100">
                                    {{ __('Inactive') }}
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Description') }}
                        </dt>
                        <dd class="whitespace-pre-line rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ $account->description ?: __('No description provided.') }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Account Metrics') }}
                </h2>

                <dl class="mt-4 space-y-4 text-sm text-gray-600 dark:text-gray-300">
                    <div class="flex items-center justify-between">
                        <dt>{{ __('Current Balance') }}</dt>
                        <dd class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format((float) $account->balance, 2) }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Opening Balance') }}</dt>
                        <dd class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format((float) $account->opening_balance, 2) }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Created At') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ optional($account->created_at)?->format('F j, Y, g:i A') ?? __('—') }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt>{{ __('Last Updated') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            {{ optional($account->updated_at)?->diffForHumans() ?? __('—') }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <form
                        method="POST"
                        action="{{ route('accounts.destroy', $account) }}"
                        x-data
                        x-on:modal-confirm.window="if ($event.detail?.id === 'delete-account-{{ $account->id }}') { $el.submit() }"
                    >
                        @csrf
                        @method('DELETE')

                        <x-button
                            type="danger"
                            buttonType="button"
                            class="w-full sm:w-auto"
                            @click="$dispatch('open-modal', { id: 'delete-account-{{ $account->id }}' })"
                        >
                            {{ __('Delete Account') }}
                        </x-button>

                        <x-modal
                            id="delete-account-{{ $account->id }}"
                            title="{{ __('Delete Account') }}"
                            confirmText="{{ __('Delete') }}"
                            cancelText="{{ __('Cancel') }}"
                            confirmColor="red"
                        >
                            {{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $account->name]) }}
                        </x-modal>
                    </form>

                    <x-button
                        tag="a"
                        href="{{ route('accounts.edit', $account) }}"
                        class="w-full sm:w-auto"
                    >
                        {{ __('Edit Account') }}
                    </x-button>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>

