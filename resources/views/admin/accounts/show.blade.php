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
                            {{ $account->bank_name ?: __('—') }}
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
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center ">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Transactions') }}
                </h2>

                

                <x-table.search
                    class="w-full sm:w-auto"
                    :action="route('accounts.show', $account)"
                    :value="$search"
                    :placeholder="__('Search transactions...')"
                />
                <x-button
                        buttonType="button"
                        class="w-full sm:w-auto"
                        @click="$dispatch('open-modal', { id: 'create-transaction-{{ $account->id }}' })"
                    >
                        {{ __('Add Transaction') }}
                    </x-button>
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
        redirect-input="from_account"
    />

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

                // Income vs Expense Chart
                const incomeExpenseCtx = document.getElementById('incomeExpenseChart');
                if (incomeExpenseCtx) {
                    const incomeExpenseData = @json($incomeExpenseData);
                    const income = parseFloat(incomeExpenseData.income || 0);
                    const expense = parseFloat(incomeExpenseData.expense || 0);

                    new Chart(incomeExpenseCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Income', 'Expense'],
                            datasets: [{
                                data: [income, expense],
                                backgroundColor: [
                                    'rgb(34, 197, 94)',
                                    'rgb(239, 68, 68)'
                                ],
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
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = parseFloat(context.parsed || 0).toLocaleString('en-US', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            });
                                            const total = income + expense;
                                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                            return label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-layouts.app>

