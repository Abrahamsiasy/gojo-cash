{{-- <x-layouts.app>
    <div class="">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Companies') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('View componys linked to your expense management system.') }}
        </p>
    </div>

</x-layouts.app> --}}

<x-layouts.app>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    {{ $company->name }}
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Company overview and performance metrics') }}
                </p>
            </div>
            <a href="{{ route('companies.index') }}"
                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                ← {{ __('Back to companies') }}
            </a>
        </div>

        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Accounts') }}</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ number_format($metrics['total_accounts']) }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Active: :active | Inactive: :inactive', ['active' => $metrics['active_accounts'], 'inactive' => $metrics['inactive_accounts']]) }}
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Balance') }}</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ number_format($metrics['total_balance'], 2) }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Across all company accounts') }}
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Transactions') }}</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ number_format($metrics['total_transactions']) }}
                </p>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                    <p>{{ __('Income: :amount', ['amount' => number_format($metrics['total_income'], 2)]) }}</p>
                    <p>{{ __('Expense: :amount', ['amount' => number_format($metrics['total_expense'], 2)]) }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Status') }}</p>
                <div class="mt-2">
                    @if($company->status)
                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800 dark:bg-green-700/40 dark:text-green-100">
                            {{ __('Active') }}
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-800 dark:bg-red-700/40 dark:text-red-100">
                            {{ __('Inactive') }}
                        </span>
                    @endif
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Trial ends: :date', ['date' => $company->trial_ends_at ? $company->trial_ends_at->format('M j, Y') : '—']) }}
                </p>
            </div>
        </div>

        {{-- Detail Grid --}}
        <div class="grid gap-6 lg:grid-cols-3">
            <section class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white/80 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Company Information') }}
                </h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ __('Company Name') }}
                        </p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $company->name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ __('Slug') }}
                        </p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 break-all">
                            {{ $company->slug }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ __('Created At') }}
                        </p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $company->created_at->format('F j, Y, g:i A') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ __('Last Updated') }}
                        </p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $company->updated_at->format('F j, Y, g:i A') }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white/80 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Quick Actions') }}
                </h2>
                <div class="space-y-3">
                    <x-button tag="a" href="{{ route('companies.edit', $company) }}" class="w-full justify-center">
                        {{ __('Edit Company') }}
                    </x-button>

                    <x-button
                        buttonType="button"
                        class="w-full justify-center"
                        @click="$dispatch('open-modal', { id: 'create-company-account-{{ $company->id }}' })"
                    >
                        {{ __('Add Account') }}
                    </x-button>

                    <x-button
                        buttonType="button"
                        class="w-full justify-center"
                        @click="$dispatch('open-modal', { id: 'create-company-transaction-{{ $company->id }}' })"
                    >
                        {{ __('Add Transaction') }}
                    </x-button>

                    
                    <form
                        method="POST"
                        action="{{ route('companies.destroy', $company) }}"
                        x-data
                        x-on:modal-confirm.window="if ($event.detail?.id === 'delete-company-{{ $company->id }}') { $el.submit() }"
                    >
                        @csrf
                        @method('DELETE')
                        <x-button
                            type="danger"
                            buttonType="button"
                            class="w-full justify-center"
                            @click="$dispatch('open-modal', { id: 'delete-company-{{ $company->id }}' })"
                        >
                            {{ __('Delete Company') }}
                        </x-button>

                        <x-modal
                            id="delete-company-{{ $company->id }}"
                            title="{{ __('Delete Company') }}"
                            confirmText="{{ __('Delete') }}"
                            cancelText="{{ __('Cancel') }}"
                            confirmColor="red"
                        >
                            {{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $company->name]) }}
                        </x-modal>
                    </form>
                </div>
            </section>
        </div>

        {{-- Accounts Section --}}
        <div class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Accounts for :company', ['company' => $company->name]) }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Total accounts: :count', ['count' => $company->accounts_count]) }}
                    </p>
                </div>

                <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <x-table.search
                        class="w-full sm:w-auto"
                        :action="route('companies.show', $company)"
                        :value="$search"
                        :placeholder="__('Search accounts...')"
                    />

                    <x-button
                        buttonType="button"
                        class="w-full sm:w-auto"
                        @click="$dispatch('open-modal', { id: 'create-company-account-{{ $company->id }}' })"
                    >
                        {{ __('Add Account') }}
                    </x-button>

                    <x-button
                        buttonType="button"
                        class="w-full sm:w-auto"
                        @click="$dispatch('open-modal', { id: 'create-company-transaction-{{ $company->id }}' })"
                    >
                        {{ __('Add Transaction') }}
                    </x-button>
                </div>
            </div>

            <x-table
                :headers="$headers"
                :rows="$rows"
                :actions="['view' => true, 'edit' => true]"
                :paginator="$accounts"
            />
        </div>

        @include('admin.companies.partials.create-account-modal')

        <x-transactions.modal
            :modal-id="'create-company-transaction-'.$company->id"
            :company-id="$company->id"
            :company-name="$company->name"
            :account-options="$companyAccounts"
            :categories="$transactionCategories"
            :transfer-accounts="$transferAccounts"
            :statuses="$statuses"
            redirect-input="from_company"
        />
    </div>
</x-layouts.app>
