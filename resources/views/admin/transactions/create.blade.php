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
        <x-forms.select label="Company" name="company_id" :options="$companies" placeholder="Select company" />

        <x-forms.select label="Account" name="account_id" :options="$accounts" placeholder="Select account" />

        {{-- Transfer Toggle --}}
        <div class="flex items-center space-x-2">
            <input type="checkbox" id="is_transfer" name="is_transfer" value="1" onchange="toggleTransferMode()"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
            <label for="is_transfer" class="text-gray-700 dark:text-gray-300 font-medium">
                Is Transfer?
            </label>
        </div>

        {{-- Related Account (only enabled if transfer) --}}
        <x-forms.select label="Related Account (for transfer)" name="related_account_id" :options="$accounts"
            placeholder="Select related account" class="w-full" />

        {{-- Transaction Category (disabled if transfer) --}}
        <x-forms.select label="Transaction Category" name="transaction_category_id"
            :options="$categories" placeholder="Select Transaction Category" class="w-full" />
    </div>

    {{-- Second Column --}}
    <div class="space-y-4">
        <x-forms.input label="Amount" name="amount" type="number" step="0.01" placeholder="Enter amount" />
        <x-forms.input label="Transaction Id" name="transaction_id" type="number" step="1" placeholder="Enter Transactin Id" />
        <x-forms.input label="Date" name="date" type="date" />
        <x-forms.select label="Status" name="status" :options="$statuses" placeholder="Select status" />
    </div>
    

    {{-- Full width description and button --}}
    <div class="md:col-span-2 space-y-4">
        <x-forms.textarea label="Description" name="description" placeholder="Enter description (optional)" />
        <div class="flex justify-end mt-4">
            <x-button>Save Transaction</x-button>
        </div>
    </div>
</form>

{{-- Script for enabling/disabling dropdowns --}}
<script>
    function toggleTransferMode() {
        const isTransfer = document.getElementById('is_transfer').checked;
        const categorySelect = document.querySelector('[name="transaction_category_id"]');
        const relatedAccountSelect = document.querySelector('[name="related_account_id"]');

        if (isTransfer) {
            categorySelect.disabled = true;
            relatedAccountSelect.disabled = false;
        } else {
            categorySelect.disabled = false;
            relatedAccountSelect.disabled = true;
        }
    }

    // Run on page load (preserve old state if validation fails)
    document.addEventListener('DOMContentLoaded', toggleTransferMode);
</script>

    </div>

</x-layouts.app>