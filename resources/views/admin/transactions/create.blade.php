<x-layouts.app>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Add New Transaction') }}</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                {{ __('Record a new income, expense, or transfer transaction.') }}
            </p>
        </div>

        <a href="{{ route('transactions.index') }}"
            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
            ← {{ __('Back to transactions') }}
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4">
        <form method="POST" action="{{ route('transactions.store') }}"
            class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
            @csrf

            {{-- First Column --}}
            <div class="space-y-4">
                <x-forms.select label="Company" name="company_id" :options="$companies" placeholder="Select company"
                    class="w-full" />

                <x-forms.select label="Account" name="account_id" :options="$accounts" placeholder="Select account"
                    class="w-full" />

                <x-forms.select label="Related Account (for transfer)" name="related_account_id" :options="$accounts"
                    placeholder="Select related account (optional)" class="w-full" />

                <x-forms.select label="Transaction Category" name="transaction_category_id" :options="$categories" placeholder="Select Transaction category"
                    class="w-full" />
            </div>

            {{-- Second Column --}}
            <div class="space-y-4">
                <x-forms.select label="Transaction Type" name="type" :options="$transactionTypes"
                    placeholder="Select type" class="w-full" />

                <x-forms.input label="Amount" name="amount" type="number" step="0.01" placeholder="Enter amount"
                    class="w-full" />

                <x-forms.input label="Date" name="date" type="date" class="w-full" />

                <x-forms.select label="Status" name="status" :options="$statuses" placeholder="Select status"
                    class="w-full" />
            </div>

            {{-- Full width description and button --}}
            <div class="md:col-span-2 space-y-4">
                <x-forms.textarea label="Description" name="description" placeholder="Enter description (optional)"
                    class="w-full" />

                <div class="flex justify-end mt-4">
                    <x-button>
                        Save Transaction
                    </x-button>
                </div>
            </div>
        </form>
    </div>

</x-layouts.app>