<x-layouts.app>
    <div class="mb-6 space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    {{ __('Client Details') }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ __('View client information and their transactions.') }}
                </p>
            </div>

            <a href="{{ route('clients.index') }}"
                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                ← {{ __('Back to clients') }}
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
                    <a href="{{ route('clients.show', $client) }}" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
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
                    action="{{ route('clients.show', $client) }}"
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
                                label="{{ __('Account') }}"
                                name="filter_account_id"
                                :options="$accounts"
                                :selected="$filters['account_id'] ?? null"
                                placeholder="{{ __('All Accounts') }}"
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
                        <a href="{{ route('clients.show', $client) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                            {{ __('Reset') }}
                        </a>
                        <x-button type="submit" class="px-4 py-2">
                            {{ __('Apply Filters') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Transaction Statistics Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Total Income') }}</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ number_format((float) $stats['total_income'], 2) }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __(':count transactions', ['count' => $stats['income_count']]) }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Total Expense') }}</div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                    {{ number_format((float) $stats['total_expense'], 2) }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __(':count transactions', ['count' => $stats['expense_count']]) }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Net Amount') }}</div>
                <div class="text-2xl font-bold {{ $stats['net_amount'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ number_format((float) $stats['net_amount'], 2) }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Income - Expense') }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Total Transactions') }}</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $stats['transaction_count'] }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('All time') }}
                </div>
            </div>
        </div>

        {{-- Client Information --}}
        <div class="grid gap-6 lg:grid-cols-3 mb-6">
            <section class="lg:col-span-2 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Client Information') }}
                </h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                            {{ __('Name') }}
                        </div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $client->name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                            {{ __('Email') }}
                        </div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $client->email ?? __('—') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                            {{ __('Company') }}
                        </div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            @if ($client->company)
                                <a href="{{ route('companies.show', $client->company) }}"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $client->company->name }}
                                </a>
                            @else
                                <span class="text-gray-400">{{ __('—') }}</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                            {{ __('Address') }}
                        </div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $client->address ?? __('—') }}
                        </div>
                    </div>
                </div>
            </section>

            {{-- Quick Actions & Metadata --}}
            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Quick Actions') }}
                </h2>

                <div class="space-y-3 mb-6">
                    <x-button
                        tag="a"
                        href="{{ route('clients.edit', $client) }}"
                        class="w-full justify-center"
                    >
                        {{ __('Edit Client') }}
                    </x-button>

                    <form
                        method="POST"
                        action="{{ route('clients.destroy', $client) }}"
                        x-data
                        x-on:modal-confirm.window="if ($event.detail?.id === 'delete-client-{{ $client->id }}') { $el.submit() }"
                    >
                        @csrf
                        @method('DELETE')

                        <x-button
                            type="danger"
                            buttonType="button"
                            class="w-full justify-center"
                            @click="$dispatch('open-modal', { id: 'delete-client-{{ $client->id }}' })"
                        >
                            {{ __('Delete Client') }}
                        </x-button>

                        <x-modal
                            id="delete-client-{{ $client->id }}"
                            title="{{ __('Delete Client') }}"
                            confirmText="{{ __('Delete') }}"
                            cancelText="{{ __('Cancel') }}"
                            confirmColor="red"
                        >
                            {{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $client->name]) }}
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
                                {{ optional($client->created_at)?->format('M j, Y') ?? __('—') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('Updated') }}</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ optional($client->updated_at)?->diffForHumans() ?? __('—') }}
                            </span>
                        </div>
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

                <x-table.search
                    class="w-full sm:w-auto"
                    :action="route('clients.show', $client)"
                    :value="$search"
                    :placeholder="__('Search transactions...')"
                />
            </div>

            @if($transactions->count() > 0)
                <x-table
                    :headers="$headers"
                    :rows="$rows"
                    :actions="['view' => true, 'edit' => true, 'delete' => true]"
                    :paginator="$transactions"
                />
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('No transactions') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('This client has no transactions yet.') }}</p>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
