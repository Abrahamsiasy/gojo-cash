<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Generate Invoice') }}</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                {{ __('Create a new invoice from a transaction or custom data.') }}
            </p>
        </div>

        <a href="{{ route('invoices.index') }}"
            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
            ← {{ __('Back to invoices') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
            <ul class="text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('invoices.store') }}" class="space-y-6" x-data="{
        generateFromTransaction: false,
        toggleMode() {
            this.generateFromTransaction = !this.generateFromTransaction;
        }
    }">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('How do you want to create this invoice?') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ __('Choose whether to create an invoice from an existing transaction or create a custom invoice from scratch.') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <label class="flex items-start p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:border-blue-500 dark:hover:border-blue-400 transition-colors"
                    :class="!generateFromTransaction ? 'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/20' : ''">
                    <input type="radio" name="source" value="custom" x-model="generateFromTransaction" :value="false"
                        @change="toggleMode()" checked
                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Custom Invoice') }}</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ __('Create a new invoice with full control over items, amounts, and details.') }}
                        </span>
                    </div>
                </label>
                <label class="flex items-start p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg cursor-not-allowed opacity-50">
                    <input type="radio" name="source" value="transaction" disabled
                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('From Transaction') }}</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ __('Quickly generate an invoice from an existing expense or income transaction.') }}
                        </span>
                    </div>
                </label>
            </div>
        </div>

        {{-- Company Selection (Always visible) --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Company & Template') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-forms.select label="Company" name="company_id" :options="$companies" placeholder="Select company" required />

                <x-forms.select label="Invoice Template" name="invoice_template_id"
                    :options="collect($templates)->mapWithKeys(fn($t) => [$t->id => $t->name])->toArray()"
                    placeholder="Select template (optional)" />
            </div>
        </div>

        {{-- Transaction Selection (shown when generateFromTransaction is true) --}}
        <div x-show="generateFromTransaction" x-transition class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Select Transactions') }}</h2>
            <x-invoices.transaction-selector :accounts="$accounts" :transactions="$transactions" />
        </div>

        {{-- Custom Invoice Form (shown when generateFromTransaction is false) --}}
        <div x-show="!generateFromTransaction" x-transition class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Basic Information') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-forms.input label="Invoice Number" name="invoice_number" placeholder="Auto-generated if empty" />

                    <x-forms.select label="Invoice Type" name="invoice_type"
                        :options="[
                            'standard' => 'Standard Invoice',
                            'proforma' => 'Proforma Invoice',
                            'credit_note' => 'Credit Note',
                            'recurring' => 'Recurring Invoice',
                            'progress' => 'Progress/Stage Invoice',
                        ]" placeholder="Select type" />

                    <x-forms.input label="Issue Date" name="issue_date" type="date" required />

                    <x-forms.input label="Due Date" name="due_date" type="date" />

                    <x-forms.input label="Reference Number" name="reference_number" placeholder="Optional reference" />
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Customer Information') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-forms.select label="Client" name="client_id" :options="$clients" placeholder="Select client (optional)" />

                    <x-forms.input label="Customer Name" name="customer_name" placeholder="Customer name" />

                    <x-forms.input label="Customer Email" name="customer_email" type="email" placeholder="Email address" />

                    <x-forms.input label="Customer Phone" name="customer_phone" placeholder="Phone number" />

                    <x-forms.input label="Customer Address" name="customer_address" placeholder="Full address" />
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Invoice Items') }}</h2>
                <div x-data="{
                    items: [{ description: '', quantity: 1, unit_price: 0, total: 0 }],
                    addItem() {
                        this.items.push({ description: '', quantity: 1, unit_price: 0, total: 0 });
                    },
                    removeItem(index) {
                        if (this.items.length > 1) {
                            this.items.splice(index, 1);
                        }
                    },
                    calculateTotal(item) {
                        item.total = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
                    },
                    getSubtotal() {
                        return this.items.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
                    }
                }">
                    <div class="space-y-4">
                        <div x-show="items.length > 0">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="grid grid-cols-12 gap-4 items-end border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800 mb-4">
                                    <div class="col-span-5">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ __('Description') }}
                                        </label>
                                        <input type="text" x-model="item.description"
                                            :name="`items[${index}][description]`"
                                            class="w-full px-4 py-2 rounded-lg text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Item description"
                                            required>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ __('Quantity') }}
                                        </label>
                                        <input type="number" x-model="item.quantity" step="0.01" min="0.01"
                                            :name="`items[${index}][quantity]`"
                                            @input="calculateTotal(item)"
                                            class="w-full px-4 py-2 rounded-lg text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="1.00"
                                            required>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ __('Unit Price') }}
                                        </label>
                                        <input type="number" x-model="item.unit_price" step="0.01" min="0"
                                            :name="`items[${index}][unit_price]`"
                                            @input="calculateTotal(item)"
                                            class="w-full px-4 py-2 rounded-lg text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="0.00"
                                            required>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ __('Total') }}
                                        </label>
                                        <input type="text" x-model="item.total.toFixed(2)" readonly
                                            class="w-full px-4 py-2 rounded-lg text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 cursor-not-allowed font-semibold"
                                            placeholder="0.00">
                                        <input type="hidden" :name="`items[${index}][total]`" :value="item.total">
                                    </div>
                                    <div class="col-span-1">
                                        <button type="button" @click="removeItem(index)"
                                            class="w-full px-3 py-2 text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors border border-red-200 dark:border-red-800">
                                            {{ __('Remove') }}
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="addItem()"
                            class="w-full px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 border-2 border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                            + {{ __('Add Item') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Totals') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-forms.input label="Tax Rate (%)" name="tax_rate" type="number" step="0.01" min="0" max="100" placeholder="e.g., 10" />

                    <x-forms.input label="Discount Amount" name="discount_amount" type="number" step="0.01" min="0" placeholder="0.00" />

                    <x-forms.input label="Currency" name="currency" placeholder="ETB" value="ETB" maxlength="5" />
                </div>
            </div>

            {{-- Additional Information (Optional) --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                x-data="{ showAdditional: false }">
                <button type="button" @click="showAdditional = !showAdditional"
                    class="flex items-center justify-between w-full text-left">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Additional Information') }}
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ __('Optional') }})</span>
                    </h2>
                    <i :class="showAdditional ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"
                        class="text-gray-500 dark:text-gray-400"></i>
                </button>
                <div x-show="showAdditional" x-transition class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-forms.input label="Terms and Conditions" name="terms_and_conditions"
                        placeholder="Payment terms (optional)..." />

                    <x-forms.input label="Bank Details" name="bank_details"
                        placeholder="Bank account information (optional)..." />

                    <x-forms.input label="Notes" name="notes" placeholder="Additional notes (optional)..." />
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('invoices.index') }}"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                {{ __('Cancel') }}
            </a>
            <x-button type="submit">
                {{ __('Generate Invoice') }}
            </x-button>
        </div>
    </form>
</x-layouts.app>

