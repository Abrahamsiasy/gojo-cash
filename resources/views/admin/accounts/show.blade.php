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

        {{-- Filters Section --}}
        <div x-data="{ filtersOpen: {{ !empty(array_filter($filters ?? [])) ? 'true' : 'false' }} }" class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <button
                    @click="filtersOpen = !filtersOpen"
                    class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': filtersOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    {{ __('Filters') }}
                </button>
                @if (!empty(array_filter($filters ?? [])))
                    <a href="{{ route('accounts.show', $account) }}" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                        {{ __('Clear Filters') }}
                    </a>
                @endif
            </div>

            <div
                x-show="filtersOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-800/50"
            >
                <form
                    method="GET"
                    action="{{ route('accounts.show', $account) }}"
                    class="space-y-4"
                    x-data="{
                        dateRange: 'custom',
                        dateFrom: '{{ $filters['date_from'] ?? '' }}',
                        dateTo: '{{ $filters['date_to'] ?? '' }}',
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
                    @if ($search)
                        <input type="hidden" name="search" value="{{ $search }}">
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <x-forms.select
                                label="{{ __('Type') }}"
                                name="filter_type"
                                :options="$typeOptions"
                                :selected="$filters['type'] ?? null"
                                placeholder="{{ __('All Types') }}"
                            />
                        </div>

                        <div>
                            <x-forms.select
                                label="{{ __('Status') }}"
                                name="filter_status"
                                :options="$statuses"
                                :selected="$filters['status'] ?? null"
                                placeholder="{{ __('All Statuses') }}"
                            />
                        </div>

                        <div>
                            <x-forms.select
                                label="{{ __('Category') }}"
                                name="filter_category_id"
                                :options="$categories"
                                :selected="$filters['category_id'] ?? null"
                                placeholder="{{ __('All Categories') }}"
                            />
                        </div>

                        <div>
                            <x-forms.select
                                label="{{ __('Client') }}"
                                name="filter_client_id"
                                :options="$clients"
                                :selected="$filters['client_id'] ?? null"
                                placeholder="{{ __('All Clients') }}"
                            />
                        </div>

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

                        <div>
                            <x-forms.input
                                label="{{ __('Date From') }}"
                                name="filter_date_from"
                                type="date"
                                x-model="dateFrom"
                            />
                        </div>

                        <div>
                            <x-forms.input
                                label="{{ __('Date To') }}"
                                name="filter_date_to"
                                type="date"
                                x-model="dateTo"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('accounts.show', $account) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                            {{ __('Reset') }}
                        </a>
                        <x-button type="submit" class="px-4 py-2">
                            {{ __('Apply Filters') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Account Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Current Balance') }}</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ number_format((float) $account->balance, 2) }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Opening Balance') }}</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ number_format((float) $account->opening_balance, 2) }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Account Type') }}</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ \Illuminate\Support\Str::headline($account->account_type?->value ?? '—') }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Status') }}</div>
                <div>
                    @if ($account->is_active)
                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800 dark:bg-green-700/40 dark:text-green-100">
                            {{ __('Active') }}
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-800 dark:bg-red-700/40 dark:text-red-100">
                            {{ __('Inactive') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            {{-- Net Cash Flow --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Net Cash Flow') }}</div>
                <div class="text-2xl font-bold {{ $kpiData['net_cash_flow'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ number_format($kpiData['net_cash_flow'], 2) }}
                </div>
            </div>

            {{-- Total Income --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Total Income') }}</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ number_format($kpiData['total_income'], 2) }}
                </div>
            </div>

            {{-- Total Expense --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Total Expense') }}</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ number_format($kpiData['total_expense'], 2) }}
                </div>
            </div>

            {{-- Top Expense Category --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Top Expense') }}</div>
                <div class="truncate text-lg font-semibold text-gray-900 dark:text-gray-100" title="{{ $kpiData['top_expense_category'] }}">
                    {{ $kpiData['top_expense_category'] }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ number_format($kpiData['top_expense_amount'], 2) }}
                </div>
            </div>
        </div>

        {{-- Account Details Grid --}}
        <div class="grid gap-6 lg:grid-cols-3 mb-6">
            {{-- Account Information --}}
            <section x-data="{ open: false }" class="lg:col-span-2 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Account Information') }}
                    </h2>
                    <button @click="open = !open" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                                {{ __('Account Name') }}
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $account->name }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                                {{ __('Company') }}
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                @if ($account->company)
                                    <a href="{{ route('companies.show', $account->company) }}"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ $account->company->name }}
                                    </a>
                                @else
                                    <span class="text-gray-400">{{ __('—') }}</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                                {{ __('Account Number') }}
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $account->account_number ?: __('—') }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                                {{ __('Bank Name') }}
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $account->bank->name ?: __('—') }}
                            </div>
                        </div>
                    </div>

                    @if ($account->description)
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                                {{ __('Description') }}
                            </div>
                            <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                {{ $account->description }}
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Quick Actions & Metadata --}}
            <section x-data="{ open: false }" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Quick Actions') }}
                    </h2>
                    <button @click="open = !open" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                >
                    <div class="space-y-3 mb-6">
                        <x-button
                            buttonType="button"
                            class="w-full justify-center"
                            @click="$dispatch('open-modal', { id: 'create-transaction-{{ $account->id }}' })"
                        >
                            {{ __('Add Transaction') }}
                        </x-button>

                        <x-button
                            tag="a"
                            href="{{ route('accounts.edit', $account) }}"
                            class="w-full justify-center"
                        >
                            {{ __('Edit Account') }}
                        </x-button>

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
                                class="w-full justify-center"
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
                    </div>

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
                            {{ __('Metadata') }}
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">{{ __('Created') }}</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ optional($account->created_at)?->format('M j, Y') ?? __('—') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">{{ __('Updated') }}</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ optional($account->updated_at)?->diffForHumans() ?? __('—') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        {{-- @dd($transactionsByCategoryData); --}}

        {{-- Charts Section --}}
        <div class="grid gap-6 lg:grid-cols-2 mb-6">


            {{-- Transactions by Category Chart --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Transactions by Category (Last 12 Months)') }}
                </h2>
                <div class="relative" style="height: 300px; max-height: 300px;">
                    <canvas id="transactionsByCategoryChart"></canvas>
                    <div id="transactionsByCategoryChartEmpty" class="hidden absolute inset-0 flex items-center justify-center">
                        <p class="text-gray-500 dark:text-gray-400">{{ __('No transaction data available') }}</p>
                    </div>
                </div>
            </section>

            {{-- Income vs Expense Chart --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Income vs Expense (Last 12 Months)') }}
                </h2>
                <div class="relative" style="height: 300px; max-height: 300px;">
                    <canvas id="incomeExpenseChart"></canvas>
                </div>
            </section>
        </div>

        {{-- Transactions Table Section --}}
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Transactions') }}
                </h2>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <x-table.search
                        class="w-full sm:w-auto"
                        :action="route('accounts.show', $account)"
                        :value="$search"
                        :placeholder="__('Search transactions...')"
                    />
                    @php
                        $exportUrl = route('accounts.export-transactions', $account);
                        $exportParams = [];
                        if ($search) {
                            $exportParams['search'] = $search;
                        }
                        foreach ($filters ?? [] as $key => $value) {
                            if ($value !== null && $value !== '') {
                                $exportParams['filter_' . $key] = $value;
                            }
                        }
                        if (!empty($exportParams)) {
                            $exportUrl .= '?' . http_build_query($exportParams);
                        }
                    @endphp
                    <x-button
                        tag="a"
                        href="{{ $exportUrl }}"
                        class="w-full sm:w-auto"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('Export CSV') }}
                    </x-button>
                    <x-button
                        buttonType="button"
                        class="w-full sm:w-auto"
                        @click="$dispatch('open-modal', { id: 'create-transaction-{{ $account->id }}' })"
                    >
                        {{ __('Add Transaction') }}
                    </x-button>
                </div>
            </div>

            {{-- Filters Section --}}


            <x-table
                :headers="$headers"
                :rows="$rows"
                :actions="['view' => true, 'edit' => true, 'delete' => true]"
                :paginator="$transactions"
            />
        </section>
    </div>

    {{-- Reusable Transaction Modal --}}
    <x-transactions.modal
        :modal-id="'create-transaction-'.$account->id"
        :company-id="$account->company_id"
        :company-name="$account->company?->name"
        :account-id="$account->id"
        :account-name="$account->name"
        :categories="$categories"
        :transfer-accounts="$transferAccounts"
        :statuses="$statuses"
        :clients="$clients"
        redirect-input="from_account"
    />

    @push('scripts')
        <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {


                // Transactions by Category Chart
                const transactionsByCategoryCtx = document.getElementById('transactionsByCategoryChart');
                if (transactionsByCategoryCtx) {
                    const categoryData = @json($transactionsByCategoryData);
                    
                    if (categoryData && categoryData.length > 0) {
                        const labels = categoryData.map(item => item.category_name);
                        const amounts = categoryData.map(item => parseFloat(item.total_amount));
                        const categoryTypes = categoryData.map(item => item.category_type);
                        
                        // Generate colors based on category type
                        const backgroundColors = categoryTypes.map(type => {
                            if (type === 'income') {
                                return 'rgb(34, 197, 94)'; // Green for income
                            } else if (type === 'expense') {
                                return 'rgb(239, 68, 68)'; // Red for expense
                            } else {
                                return 'rgb(59, 130, 246)'; // Blue for other types
                            }
                        });

                        new Chart(transactionsByCategoryCtx, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: amounts,
                                    backgroundColor: backgroundColors,
                                    borderWidth: 2,
                                    borderColor: 'rgb(255, 255, 255)'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 1.5,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 12,
                                            padding: 10
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = parseFloat(context.parsed || 0).toLocaleString('en-US', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                });
                                                const total = amounts.reduce((a, b) => a + b, 0);
                                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                                const categoryType = categoryTypes[context.dataIndex];
                                                return label + ' (' + categoryType + '): ' + value + ' (' + percentage + '%)';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        // Show empty message
                        const emptyMessage = document.getElementById('transactionsByCategoryChartEmpty');
                        if (emptyMessage) {
                            emptyMessage.classList.remove('hidden');
                        }
                    }
                }

                // Income vs Expense Chart
                const incomeExpenseCtx = document.getElementById('incomeExpenseChart');
                if (incomeExpenseCtx) {
                    const chartData = @json($incomeExpenseChartData);
                    
                    new Chart(incomeExpenseCtx, {
                        type: 'bar',
                        data: {
                            labels: chartData.labels,
                            datasets: [
                                {
                                    label: 'Income',
                                    data: chartData.income,
                                    backgroundColor: 'rgba(34, 197, 94, 0.7)', // Green
                                    borderColor: 'rgb(34, 197, 94)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    barPercentage: 0.6,
                                    categoryPercentage: 0.8
                                },
                                {
                                    label: 'Expense',
                                    data: chartData.expense,
                                    backgroundColor: 'rgba(239, 68, 68, 0.7)', // Red
                                    borderColor: 'rgb(239, 68, 68)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    barPercentage: 0.6,
                                    categoryPercentage: 0.8
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += parseFloat(context.parsed.y).toLocaleString('en-US', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                });
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return parseFloat(value).toLocaleString('en-US', {
                                                minimumFractionDigits: 0,
                                                maximumFractionDigits: 0
                                            });
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index',
                            },
                        }
                    });
                }
            });
        </script>
    @endpush
</x-layouts.app>

