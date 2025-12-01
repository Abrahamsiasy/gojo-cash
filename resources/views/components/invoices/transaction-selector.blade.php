@props([
    'accounts' => [],
    'transactions' => [],
])

<div x-data="{
    selectedAccountId: null,
    selectedTransactionIds: [],
    availableTransactions: @js($transactions),
    filteredTransactions: [],
    
    init() {
        this.updateFilteredTransactions();
    },
    
    onAccountChange() {
        this.selectedTransactionIds = [];
        this.updateFilteredTransactions();
    },
    
    updateFilteredTransactions() {
        if (!this.selectedAccountId) {
            this.filteredTransactions = [];
            return;
        }
        
        this.filteredTransactions = this.availableTransactions.filter(
            transaction => transaction.account_id == this.selectedAccountId
        );
    },
    
    toggleTransaction(transactionId) {
        const index = this.selectedTransactionIds.indexOf(transactionId);
        if (index > -1) {
            this.selectedTransactionIds.splice(index, 1);
        } else {
            this.selectedTransactionIds.push(transactionId);
        }
    },
    
    isSelected(transactionId) {
        return this.selectedTransactionIds.includes(transactionId);
    },
    
    getSelectedTransactions() {
        return this.availableTransactions.filter(
            t => this.selectedTransactionIds.includes(t.id)
        );
    },
    
    getTotalAmount() {
        return this.getSelectedTransactions().reduce((sum, t) => sum + parseFloat(t.amount || 0), 0);
    }
}">
    <div class="space-y-4">
        <div>
            <x-forms.select 
                label="{{ __('Account') }}" 
                name="account_id" 
                :options="$accounts" 
                placeholder="{{ __('Select account') }}"
                x-model="selectedAccountId"
                @change="onAccountChange()"
                required />
        </div>

        <div x-show="selectedAccountId && filteredTransactions.length > 0" x-transition>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ __('Select Transactions') }} <span class="text-red-500">*</span>
            </label>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                {{ __('Select one or more transactions to include in this invoice.') }}
            </p>
            
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg max-h-96 overflow-y-auto">
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="transaction in filteredTransactions" :key="transaction.id">
                        <label class="flex items-center p-4 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
                            <input 
                                type="checkbox" 
                                :value="transaction.id"
                                x-model="selectedTransactionIds"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <span x-text="'TXN-' + String(transaction.id).padStart(5, '0')"></span>
                                            <span x-show="transaction.client" class="text-gray-500 dark:text-gray-400">
                                                - <span x-text="transaction.client?.name || 'No Client'"></span>
                                            </span>
                                        </span>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="transaction.description || 'No description'"></p>
                                    </div>
                                    <div class="text-right ml-4">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100" 
                                            x-text="new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(transaction.amount || 0)"></span>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="transaction.date ? new Date(transaction.date).toLocaleDateString() : ''"></p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </template>
                </div>
            </div>
            
            <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Selected Transactions') }}: <span x-text="selectedTransactionIds.length"></span>
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Amount') }}:</span>
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400 ml-2" 
                            x-text="new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(getTotalAmount())"></span>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="transaction_ids" :value="JSON.stringify(selectedTransactionIds)">
        </div>

        <div x-show="selectedAccountId && filteredTransactions.length === 0" x-transition class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                {{ __('No transactions found for this account.') }}
            </p>
        </div>
    </div>
</div>

