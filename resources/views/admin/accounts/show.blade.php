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

        {{-- Account Details Grid --}}
        <div class="grid gap-6 lg:grid-cols-3 mb-6">
            {{-- Account Information --}}
            <section class="lg:col-span-2 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Account Information') }}
                </h2>

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
            </section>

            {{-- Quick Actions & Metadata --}}
            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Quick Actions') }}
                </h2>

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
            </section>
        </div>
        {{-- @dd($transactionsByCategoryData); --}}

        {{-- Charts Section --}}
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Balance Over Time Chart --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Balance Over Time') }}
                </h2>
                <div class="relative" style="height: 300px; max-height: 300px;">
                    <canvas id="balanceChart"></canvas>
                </div>
            </section>

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
            <div x-data="{ filtersOpen: {{ !empty(array_filter($filters ?? [])) ? 'true' : 'false' }} }" class="mb-4">
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
                    <form method="GET" action="{{ route('accounts.show', $account) }}" class="space-y-4">
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
                                <x-forms.input
                                    label="{{ __('Date From') }}"
                                    name="filter_date_from"
                                    type="date"
                                    :value="$filters['date_from'] ?? null"
                                />
                            </div>

                            <div>
                                <x-forms.input
                                    label="{{ __('Date To') }}"
                                    name="filter_date_to"
                                    type="date"
                                    :value="$filters['date_to'] ?? null"
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
                // Balance Over Time Chart
                const balanceCtx = document.getElementById('balanceChart');
                if (balanceCtx) {
                    const balanceData = @json($balanceChartData);
                    const labels = balanceData.map(item => {
                        const [year, month] = item.month.split('-');
                        return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    });
                    const balances = balanceData.map(item => parseFloat(item.balance));

                    new Chart(balanceCtx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Balance',
                                data: balances,
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 2,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'Balance: ' + parseFloat(context.parsed.y).toLocaleString('en-US', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            });
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    ticks: {
                                        callback: function(value) {
                                            return parseFloat(value).toLocaleString('en-US', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            });
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

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
            });
        </script>
    @endpush
</x-layouts.app>

