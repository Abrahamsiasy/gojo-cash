{{-- Company Account Creation Modal --}}
@php
    $accountModalId = 'create-company-account-' . $company->id;
@endphp

<div
    x-data="{ open: false }"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="open = false"
    x-on:open-modal.window="if ($event.detail?.id === '{{ $accountModalId }}') { open = true; }"
    x-on:close-modal.window="if (! $event.detail?.id || $event.detail.id === '{{ $accountModalId }}') { open = false }"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    style="display: none;"
>
    <div
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="mx-4 w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900"
    >
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Add Account') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Create a new account for :company', ['company' => $company->name]) }}
                    </p>
                </div>
                <button
                    @click="open = false"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('accounts.store') }}" class="grid gap-4 sm:grid-cols-2">
                @csrf

                <input type="hidden" name="company_id" value="{{ $company->id }}">
                <input type="hidden" name="from_company" value="1">

                <x-forms.input
                    label="{{ __('Account Name') }}"
                    name="name"
                    placeholder="{{ __('Enter account name') }}"
                    class="w-full"
                    required
                />

                <x-forms.input
                    label="{{ __('Account Number') }}"
                    name="account_number"
                    placeholder="{{ __('Enter account number') }}"
                    class="w-full"
                    required
                />

                <x-forms.select
                    label="{{ __('Account Type') }}"
                    name="account_type"
                    :options="$accountTypeOptions"
                    placeholder="{{ __('Select account type') }}"
                    class="w-full"
                    required
                />

                <x-forms.select
                    label="{{ __('Bank Name') }}"
                    name="bank_id"
                    :options="$banks"
                    placeholder="{{ __('Select Bank') }}"
                    class="w-full"
                    required
                />


                <x-forms.input
                    label="{{ __('Balance') }}"
                    name="balance"
                    type="number"
                    step="0.01"
                    placeholder="{{ __('Enter current balance') }}"
                    class="w-full"
                    required
                />

                <x-forms.input
                    label="{{ __('Opening Balance') }}"
                    name="opening_balance"
                    type="number"
                    step="0.01"
                    placeholder="{{ __('Enter opening balance') }}"
                    class="w-full"
                    required
                />

                <div class="sm:col-span-2">
                    <x-forms.textarea
                        label="{{ __('Description') }}"
                        name="description"
                        placeholder="{{ __('Enter description (optional)') }}"
                        rows="3"
                    />
                </div>

                <div class="sm:col-span-2 flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        @click="open = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <x-button type="submit">
                        {{ __('Save Account') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>

