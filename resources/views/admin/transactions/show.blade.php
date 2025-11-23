<x-layouts.app>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    {{ __('Transaction Details') }}
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('View detailed information about this transaction') }}
                </p>
            </div>
            <a href="{{ route('transactions.index') }}"
                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                ← {{ __('Back to transactions') }}
            </a>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        {{-- Transaction Information Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Left Column --}}
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Transaction ID') }}
                            </label>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $transaction->transaction_id ?? 'TXN-' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Type') }}
                            </label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($transaction->type === 'income') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($transaction->type === 'expense') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @else bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                    @endif">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Amount') }}
                            </label>
                            <p class="mt-1 text-2xl font-bold
                                @if($transaction->type === 'income') text-green-600 dark:text-green-400
                                @elseif($transaction->type === 'expense') text-red-600 dark:text-red-400
                                @else text-blue-600 dark:text-blue-400
                                @endif">
                                @if($transaction->type === 'expense' || $transaction->type === 'transfer')
                                    -{{ number_format((float) $transaction->amount, 2) }}
                                @else
                                    +{{ number_format((float) $transaction->amount, 2) }}
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Date') }}
                            </label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                {{ $transaction->date?->format('F j, Y') ?? __('—') }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Status') }}
                            </label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($transaction->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($transaction->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                    @endif">
                                    {{ ucfirst($transaction->status ?? 'pending') }}
                                </span>
                            </p>
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Company') }}
                            </label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                <a href="{{ $transaction->company ? route('companies.show', $transaction->company) : '#' }}"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $transaction->company->name ?? __('—') }}
                                </a>
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Account') }}
                            </label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                <a href="{{ $transaction->account ? route('accounts.show', $transaction->account) : '#' }}"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $transaction->account->name ?? __('—') }}
                                </a>
                            </p>
                        </div>

                        @if($transaction->relatedAccount)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Related Account') }}
                                </label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('accounts.show', $transaction->relatedAccount) }}"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ $transaction->relatedAccount->name }}
                                    </a>
                                </p>
                            </div>
                        @endif

                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Category') }}
                            </label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                {{ $transaction->category->name ?? __('—') }}
                            </p>
                        </div>

                        @if($transaction->client)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Client') }}
                                </label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('clients.show', $transaction->client) }}"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ $transaction->client->name }}
                                    </a>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Description --}}
                @if($transaction->description)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Description') }}
                        </label>
                        <p class="mt-2 text-gray-900 dark:text-gray-100">
                            {{ $transaction->description }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Balance Information Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Balance Information') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Previous Balance') }}
                        </label>
                        <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format((float) ($transaction->previous_balance ?? 0), 2) }}
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Transaction Amount') }}
                        </label>
                        <p class="mt-1 text-xl font-semibold
                            @if($transaction->type === 'income') text-green-600 dark:text-green-400
                            @elseif($transaction->type === 'expense') text-red-600 dark:text-red-400
                            @else text-blue-600 dark:text-blue-400
                            @endif">
                            @if($transaction->type === 'expense' || $transaction->type === 'transfer')
                                -{{ number_format((float) $transaction->amount, 2) }}
                            @else
                                +{{ number_format((float) $transaction->amount, 2) }}
                            @endif
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('New Balance') }}
                        </label>
                        <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format((float) ($transaction->new_balance ?? 0), 2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Metadata Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Transaction Metadata') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Created By') }}
                        </label>
                        <p class="mt-1 text-gray-900 dark:text-gray-100">
                            {{ $transaction->creator->name ?? __('—') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $transaction->created_at?->format('M j, Y g:i A') }}
                        </p>
                    </div>

                    @if($transaction->approver)
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Approved By') }}
                            </label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                {{ $transaction->approver->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $transaction->approved_at?->format('M j, Y g:i A') }}
                            </p>
                        </div>
                    @endif

                    @if($transaction->updater)
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Last Updated By') }}
                            </label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                {{ $transaction->updater->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $transaction->updated_at?->format('M j, Y g:i A') }}
                            </p>
                        </div>
                    @endif

                    @if($transaction->is_reconciled)
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Reconciliation Status') }}
                            </label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    {{ __('Reconciled') }}
                                </span>
                            </p>
                        </div>
                    @endif
                </div>

                @if($transaction->meta && is_array($transaction->meta) && count($transaction->meta) > 0)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2 block">
                            {{ __('Additional Metadata') }}
                        </label>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ json_encode($transaction->meta, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <form
                method="POST"
                action="{{ route('transactions.destroy', $transaction) }}"
                x-data
                x-on:modal-confirm.window="if ($event.detail?.id === 'delete-transaction-{{ $transaction->id }}') { $el.submit() }"
            >
                @csrf
                @method('DELETE')

                <x-button
                    type="danger"
                    buttonType="button"
                    class="px-4 py-2 text-sm"
                    @click="$dispatch('open-modal', { id: 'delete-transaction-{{ $transaction->id }}' })"
                >
                    {{ __('Delete Transaction') }}
                </x-button>

                <x-modal
                    id="delete-transaction-{{ $transaction->id }}"
                    title="{{ __('Delete Transaction') }}"
                    confirmText="{{ __('Delete') }}"
                    cancelText="{{ __('Cancel') }}"
                    confirmColor="red"
                >
                    {{ __('Are you sure you want to delete this transaction? This action cannot be undone.') }}
                </x-modal>
            </form>
        </div>
    </div>
</x-layouts.app>

