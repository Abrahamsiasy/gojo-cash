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

        {{-- Filters Section --}}
        <div
            x-data="{ expanded: false }"
            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-800/50"
        >
            <div class="flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    {{ __('Filters') }}
                </h2>
                <svg
                    class="w-5 h-5 text-gray-500 transform transition-transform duration-200"
                    :class="{ 'rotate-180': expanded }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <div
                x-show="expanded"
                x-collapse
                class="mt-4"
            >
                <form
                    method="GET"
                    action="{{ route('companies.show', $company) }}"
                    class="space-y-4"
                    x-data="{
                        dateRange: 'custom',
                        dateFrom: '{{ request('filter_date_from') }}',
                        dateTo: '{{ request('filter_date_to') }}',
                        updateDates() {
                            if (this.dateRange === 'custom') return;

                            const today = new Date();
                            const formatDate = (date) => {
                                const year = date.getFullYear();
                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                const day = String(date.getDate()).padStart(2, '0');
                                return `${year}-${month}-${day}`;
                            };

                            let start = new Date(today);
                            let end = new Date(today);

                            switch (this.dateRange) {
                                case 'today':
                                    break;
                                case 'yesterday':
                                    start.setDate(today.getDate() - 1);
                                    end.setDate(today.getDate() - 1);
                                    break;
                                case 'this_week':
                                    const day = today.getDay() || 7;
                                    start.setDate(today.getDate() - day + 1);
                                    end.setDate(start.getDate() + 6);
                                    break;
                                case 'last_week':
                                    const currentDay = today.getDay() || 7;
                                    start.setDate(today.getDate() - currentDay - 6);
                                    end.setDate(start.getDate() + 6);
                                    break;
                                case 'this_month':
                                    start = new Date(today.getFullYear(), today.getMonth(), 1);
                                    end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                                    break;
                                case 'last_month':
                                    start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                                    end = new Date(today.getFullYear(), today.getMonth(), 0);
                                    break;
                                case 'this_year':
                                    start = new Date(today.getFullYear(), 0, 1);
                                    end = new Date(today.getFullYear(), 11, 31);
                                    break;
                            }

                            this.dateFrom = formatDate(start);
                            this.dateTo = formatDate(end);
                        }
                    }"
                >
                    {{-- Preserve search if exists --}}
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {{-- Date Range Presets --}}
                        <div>
                            <label class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Date Range') }}
                            </label>
                            <select
                                x-model="dateRange"
                                @change="updateDates()"
                                class="w-full px-4 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="custom">{{ __('Custom') }}</option>
                                <option value="today">{{ __('Today') }}</option>
                                <option value="yesterday">{{ __('Yesterday') }}</option>
                                <option value="this_week">{{ __('This Week') }}</option>
                                <option value="last_week">{{ __('Last Week') }}</option>
                                <option value="this_month">{{ __('This Month') }}</option>
                                <option value="last_month">{{ __('Last Month') }}</option>
                                <option value="this_year">{{ __('This Year') }}</option>
                            </select>
                        </div>

                        {{-- Date From --}}
                        <div>
                            <x-forms.input
                                label="{{ __('Date From') }}"
                                name="filter_date_from"
                                type="date"
                                x-model="dateFrom"
                            />
                        </div>

                        {{-- Date To --}}
                        <div>
                            <x-forms.input
                                label="{{ __('Date To') }}"
                                name="filter_date_to"
                                type="date"
                                x-model="dateTo"
                            />
                        </div>

                        {{-- Account Filter --}}
                        <div>
                            <x-forms.select
                                label="{{ __('Account') }}"
                                name="filter_account_id"
                                :options="$companyAccounts"
                                :selected="request('filter_account_id')"
                                placeholder="{{ __('All Accounts') }}"
                            />
                        </div>

                        {{-- Category Filter --}}
                        <div>
                            <x-forms.select
                                label="{{ __('Category') }}"
                                name="filter_category_id"
                                :options="$transactionCategories"
                                :selected="request('filter_category_id')"
                                placeholder="{{ __('All Categories') }}"
                            />
                        </div>

                        {{-- Client Filter --}}
                        <div>
                            <x-forms.select
                                label="{{ __('Client') }}"
                                name="filter_client_id"
                                :options="$clients"
                                :selected="request('filter_client_id')"
                                placeholder="{{ __('All Clients') }}"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a
                            href="{{ route('companies.show', $company) }}"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                        >
                            {{ __('Clear Filters') }}
                        </a>
                        <x-button type="primary" buttonType="submit">
                            {{ __('Apply Filters') }}
                        </x-button>
                    </div>
                </form>
            </div>
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

        {{-- Charts Section --}}
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Income vs Expense --}}
            <section class="rounded-2xl border border-gray-200 bg-white/80 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Income vs Expense (Last 12 Months)') }}
                </h2>
                <div class="relative" style="height: 300px;">
                    <canvas id="incomeExpenseChart"></canvas>
                </div>
            </section>

            {{-- Income by Category Chart --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Income by Category') }}</h3>
                <div class="relative h-64 w-full">
                    <canvas id="incomeCategoryChart"></canvas>
                </div>
            </div>

            {{-- Expenses by Category Chart --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Expenses by Category') }}</h3>
                <div class="relative h-64 w-full">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            {{-- Transactions by Account --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Transactions by Account') }}</h3>
                <div class="relative h-64 w-full">
                    <canvas id="accountChart"></canvas>
                </div>
            </div>

            {{-- Financial Insights --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('Financial Insights') }}
                </h3>
                <div class="space-y-4 text-sm">
                    {{-- Net Cash Flow --}}
                    <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Net Cash Flow') }}</span>
                        <span class="font-medium {{ $financialInsights['net_cash_flow'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $financialInsights['net_cash_flow'] >= 0 ? '+' : '' }}{{ number_format($financialInsights['net_cash_flow'], 2) }}
                        </span>
                    </div>

                    {{-- Top Expense --}}
                    @if($financialInsights['top_expense_category'])
                        <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Highest Expense') }}</span>
                            <div class="text-right">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $financialInsights['top_expense_category'] }}</div>
                                <div class="text-xs text-gray-500">{{ number_format($financialInsights['top_expense_amount'], 2) }}</div>
                            </div>
                        </div>
                    @endif

                    {{-- Top Income --}}
                    @if($financialInsights['top_income_category'])
                        <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Top Income Source') }}</span>
                            <div class="text-right">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $financialInsights['top_income_category'] }}</div>
                                <div class="text-xs text-gray-500">{{ number_format($financialInsights['top_income_amount'], 2) }}</div>
                            </div>
                        </div>
                    @endif

                    {{-- Most Active Account --}}
                    @if($financialInsights['most_active_account'])
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Most Active Account') }}</span>
                            <div class="text-right">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $financialInsights['most_active_account'] }}</div>
                                <div class="text-xs text-gray-500">{{ $financialInsights['most_active_account_count'] }} {{ __('transactions') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Transactions by Type --}}
            <section class="rounded-2xl border border-gray-200 bg-white/80 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Transactions by Type') }}
                </h2>
                <div class="relative" style="height: 300px;">
                    <canvas id="typeChart"></canvas>
                </div>
            </section>
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
            :clients="$clients"
            redirect-input="from_company"
        />
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const isDark = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? '#374151' : '#e5e7eb';
                const textColor = isDark ? '#9ca3af' : '#4b5563';

                // Income vs Expense Chart
                new Chart(document.getElementById('incomeExpenseChart'), {
                    type: 'bar',
                    data: {
                        labels: @json($incomeExpenseChartData['labels']),
                        datasets: [
                            {
                                label: '{{ __('Income') }}',
                                data: @json($incomeExpenseChartData['income']),
                                backgroundColor: '#10b981',
                                borderRadius: 4,
                            },
                            {
                                label: '{{ __('Expense') }}',
                                data: @json($incomeExpenseChartData['expense']),
                                backgroundColor: '#ef4444',
                                borderRadius: 4,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            }
                        },
                        plugins: {
                            legend: {
                                labels: { color: textColor }
                            }
                        }
                    }
                });

                // Income by Category Chart
                new Chart(document.getElementById('incomeCategoryChart'), {
                    type: 'doughnut',
                    data: {
                        labels: @json($incomeByCategoryData['labels']),
                        datasets: [{
                            data: @json($incomeByCategoryData['data']),
                            backgroundColor: [
                                '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                                '#6366f1', '#ec4899', '#06b6d4', '#84cc16', '#f97316',
                                '#14b8a6', '#d946ef', '#eab308', '#f43f5e', '#a855f7'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { color: textColor }
                            }
                        }
                    }
                });

                // Expenses by Category Chart
                new Chart(document.getElementById('categoryChart'), {
                    type: 'doughnut',
                    data: {
                        labels: @json($transactionsByCategoryData['labels']),
                        datasets: [{
                            data: @json($transactionsByCategoryData['data']),
                            backgroundColor: [
                                '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                                '#6366f1', '#ec4899', '#06b6d4', '#84cc16', '#f97316',
                                '#14b8a6', '#d946ef', '#eab308', '#f43f5e', '#a855f7'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { color: textColor }
                            }
                        }
                    }
                });

                // Account Chart
                new Chart(document.getElementById('accountChart'), {
                    type: 'bar',
                    data: {
                        labels: @json($transactionsByAccountData['labels']),
                        datasets: [{
                            label: '{{ __('Transaction Count') }}',
                            data: @json($transactionsByAccountData['data']),
                            backgroundColor: '#6366f1',
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: {
                                    color: textColor,
                                    precision: 0
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });

                // Type Chart (Grouped Bar for Count & Amount)
                const typeLabels = @json($transactionsByTypeData['labels']);
                const typeCounts = @json($transactionsByTypeData['counts']);
                const typeAmounts = @json($transactionsByTypeData['amounts']);

                new Chart(document.getElementById('typeChart'), {
                    type: 'bar',
                    data: {
                        labels: typeLabels,
                        datasets: [
                            {
                                label: '{{ __('Count') }}',
                                data: typeCounts,
                                backgroundColor: '#6366f1',
                                yAxisID: 'y',
                                borderRadius: 4,
                            },
                            {
                                label: '{{ __('Amount') }}',
                                data: typeAmounts,
                                backgroundColor: '#10b981',
                                yAxisID: 'y1',
                                borderRadius: 4,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: { display: true, text: 'Count' },
                                grid: { color: gridColor },
                                ticks: { color: textColor }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: { display: true, text: 'Amount' },
                                grid: { drawOnChartArea: false }, // only want the grid lines for one axis to show up
                                ticks: { color: textColor }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: textColor }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-layouts.app>
