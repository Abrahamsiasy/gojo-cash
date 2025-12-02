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

    @if ($errors->any())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
            <ul class="text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4">
        <form method="POST" action="{{ route('transactions.store') }}" enctype="multipart/form-data"
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
        <x-forms.select label="Client" name="client_id" :options="$clients" placeholder="Select client" />
        <x-forms.input label="Amount" name="amount" type="number" step="0.01" placeholder="Enter amount" />
        <x-forms.input label="Transaction Id" name="transaction_id" type="number" step="1" placeholder="Enter Transactin Id" />
        <x-forms.input label="Date" name="date" type="date" />
        <x-forms.select label="Status" name="status" :options="$statuses" placeholder="Select status" />
    </div>
    

    {{-- Full width description and button --}}
    <div class="md:col-span-2 space-y-4">
        <x-forms.textarea label="Description" name="description" placeholder="Enter description (optional)" />

        {{-- File Attachments Section --}}
        <div
            x-data="{
                previews: [],
                handleFileSelect(event) {
                    const files = Array.from(event.target.files);
                    this.previews = [];
                    files.forEach(file => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.previews.push({
                                    name: file.name,
                                    size: file.size,
                                    url: e.target.result
                                });
                            };
                            reader.readAsDataURL(file);
                        } else {
                            this.previews.push({
                                name: file.name,
                                size: file.size,
                                url: null
                            });
                        }
                    });
                },
                removePreview(index) {
                    this.previews.splice(index, 1);
                }
            }"
            class="space-y-3"
        >
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('Attachments') }} <span class="text-gray-500 dark:text-gray-400 text-xs">({{ __('Optional') }})</span>
                </label>
                <input
                    type="file"
                    name="attachments[]"
                    multiple
                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.csv"
                    @change="handleFileSelect($event)"
                    class="block w-full text-sm text-gray-500 dark:text-gray-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-lg file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100
                        dark:file:bg-blue-900/30 dark:file:text-blue-400"
                >
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Allowed: Images (JPG, PNG, GIF, WEBP),  Max 10MB per file.') }}
                </p>
            </div>

            {{-- Image Previews --}}
            <div x-show="previews.length > 0" class="space-y-3">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Selected Files:') }}
                </p>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <template x-for="(preview, index) in previews" :key="index">
                        <div class="relative border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <template x-if="preview.url">
                                <div class="relative">
                                    <img :src="preview.url" :alt="preview.name" class="w-full h-24 object-cover">
                                    <button
                                        type="button"
                                        @click="removePreview(index)"
                                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <template x-if="!preview.url">
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 text-center">
                                    <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <button
                                        type="button"
                                        @click="removePreview(index)"
                                        class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        {{ __('Remove') }}
                                    </button>
                                </div>
                            </template>
                            <div class="p-2 bg-white dark:bg-gray-900">
                                <p class="text-xs text-gray-600 dark:text-gray-400 truncate" :title="preview.name" x-text="preview.name"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

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