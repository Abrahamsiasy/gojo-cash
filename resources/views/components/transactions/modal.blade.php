@php
    use Illuminate\Support\Str;
@endphp

@props([
    'modalId',
    'companyId',
    'clientId',
    'companyName' => null,
    'accountId' => null,
    'accountName' => null,
    'accountOptions' => [],
    'categories' => [],
    'transferAccounts' => [],
    'statuses' => [],
    'redirectInput' => null,
    'title' => __('Add Transaction'),
    'description' => null,
    'clients' => null
])

@php
    $prefix = Str::slug($modalId, '_');

    $accountSelectId = $prefix.'_account_select';
    $transferToggleId = $prefix.'_is_transfer';
    $categorySelectId = $prefix.'_category';
    $relatedSelectId = $prefix.'_related_account';
    $clientSelectId = $prefix.'_client';
@endphp

<div
    x-data="{ open: false }"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="open = false"
    x-on:open-modal.window="if ($event.detail?.id === '{{ $modalId }}') {
        open = true;
        const relatedSelect = document.getElementById('{{ $relatedSelectId }}');
        const categorySelect = document.getElementById('{{ $categorySelectId }}');
        const transferToggle = document.getElementById('{{ $transferToggleId }}');
        const clientSelect = document.getElementById('{{ $clientSelectId }}');

        if (relatedSelect) {
            relatedSelect.disabled = true;
            relatedSelect.value = '';
        }

        if (categorySelect) {
            categorySelect.disabled = false;
        }

        if (transferToggle) {
            transferToggle.checked = false;
        }
    }"
    x-on:close-modal.window="if (! $event.detail?.id || $event.detail.id === '{{ $modalId }}') { open = false }"
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
                        {{ $title }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ $description ?? ($accountName
                            ? __('Record a new transaction for :account', ['account' => $accountName])
                            : __('Record a new transaction for :company', ['company' => $companyName ?? __('this company')])) }}
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

            <form method="POST" action="{{ route('transactions.store') }}" class="space-y-4">
                @csrf

                <input type="hidden" name="company_id" value="{{ $companyId }}">

                @if ($accountId)
                    <input type="hidden" name="account_id" value="{{ $accountId }}">
                @endif

                @if ($redirectInput)
                    <input type="hidden" name="{{ $redirectInput }}" value="1">
                @endif

                <div class="grid gap-4 sm:grid-cols-2">

                    @if (! empty($accountOptions))
                        <div class="sm:col-span-2">
                            <x-forms.select
                                label="{{ __('Account') }}"
                                name="account_id"
                                id="{{ $accountSelectId }}"
                                :options="$accountOptions"
                                placeholder="{{ __('Select account') }}"
                                class="w-full"
                                required
                                x-on:change="
                                    const relatedSelect = document.getElementById('{{ $relatedSelectId }}');
                                    if (! relatedSelect) {
                                        return;
                                    }

                                    [...relatedSelect.options].forEach((option) => {
                                        if (! option.value) {
                                            return;
                                        }

                                        option.disabled = option.value === $el.value;

                                        if (option.disabled && option.selected) {
                                            relatedSelect.value = '';
                                        }
                                    });
                                "
                            />
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <div class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                id="{{ $transferToggleId }}"
                                name="is_transfer"
                                value="1"
                                x-on:change="
                                    const isTransfer = $el.checked;
                                    const categorySelect = document.getElementById('{{ $categorySelectId }}');
                                    const relatedSelect = document.getElementById('{{ $relatedSelectId }}');

                                    if (categorySelect) {
                                        categorySelect.disabled = isTransfer;
                                        if (isTransfer) {
                                            categorySelect.value = '';
                                        }
                                    }

                                    if (relatedSelect) {
                                        relatedSelect.disabled = ! isTransfer;
                                        if (! isTransfer) {
                                            relatedSelect.value = '';
                                        }
                                    }
                                "
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="{{ $transferToggleId }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Is Transfer?') }}
                            </label>
                        </div>
                    </div>

                    <div>
                        <x-forms.select
                            label="{{ __('Transaction Category') }}"
                            name="transaction_category_id"
                            id="{{ $categorySelectId }}"
                            :options="$categories"
                            placeholder="{{ __('Select category') }}"
                            class="w-full"
                        />
                    </div>
                    <div>
                        <x-forms.select
                            label="{{ __('Client') }}"
                            name="client_id"
                            id="{{ $clientSelectId }}"
                            :options="$clients"
                            placeholder="{{ __('Select client') }}"
                            class="w-full"
                        />
                    </div>

                    <div>
                        <x-forms.input
                            label="{{ __('Transaction ID (optional)') }}"
                            name="transaction_id"
                            type="text"
                            placeholder="{{ __('Enter external reference') }}"
                        />
                    </div>
                    <div>
                        <x-forms.select
                            label="{{ __('Related Account (for transfer)') }}"
                            name="related_account_id"
                            id="{{ $relatedSelectId }}"
                            :options="$transferAccounts"
                            placeholder="{{ __('Select related account') }}"
                            class="w-full"
                            disabled
                        />
                    </div>

                    <div>
                        <x-forms.input
                            label="{{ __('Amount') }}"
                            name="amount"
                            type="number"
                            step="0.01"
                            placeholder="{{ __('Enter amount') }}"
                            required
                        />
                    </div>

                    <div>
                        <x-forms.input
                            label="{{ __('Date') }}"
                            name="date"
                            type="date"
                            :value="now()->format('Y-m-d')"
                            required
                        />
                    </div>

                    <div>
                        <x-forms.select
                            label="{{ __('Status') }}"
                            name="status"
                            :options="$statuses"
                            placeholder="{{ __('Select status') }}"
                            class="w-full"
                        />
                    </div>
                </div>

                <div>
                    <x-forms.textarea
                        label="{{ __('Description') }}"
                        name="description"
                        placeholder="{{ __('Enter description (optional)') }}"
                        rows="3"
                    />
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        @click="open = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <x-button type="submit">
                        {{ __('Save Transaction') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>

