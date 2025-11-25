@php
    use Illuminate\Support\Str;
@endphp

@props([
    'modalId',
    'companyId',
    'clientId',
    'companyName' => null,
    'accountId' => null,
    'accountName' => null,
    'accountOptions' => [],
    'categories' => [],
    'transferAccounts' => [],
    'statuses' => [],
    'redirectInput' => null,
    'title' => __('Add Transaction'),
    'description' => null,
    'clients' => null
])

@php
    $prefix = Str::slug($modalId, '_');

    $accountSelectId = $prefix.'_account_select';
    $transferToggleId = $prefix.'_is_transfer';
    $categorySelectId = $prefix.'_category';
    $relatedSelectId = $prefix.'_related_account';
    $clientSelectId = $prefix.'_client';
@endphp

@php
    $hasErrors = $errors->any() && old('_token');
    $shouldOpen = $hasErrors;
@endphp

<div
    x-data="{
        open: {{ $shouldOpen ? 'true' : 'false' }},
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
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="if (!{{ $hasErrors ? 'true' : 'false' }}) { open = false }"
    x-on:open-modal.window="if ($event.detail?.id === '{{ $modalId }}') {
        open = true;
        const relatedSelect = document.getElementById('{{ $relatedSelectId }}');
        const categorySelect = document.getElementById('{{ $categorySelectId }}');
        const transferToggle = document.getElementById('{{ $transferToggleId }}');
        const clientSelect = document.getElementById('{{ $clientSelectId }}');

        if (relatedSelect) {
            relatedSelect.disabled = true;
            relatedSelect.value = '';
        }

        if (categorySelect) {
            categorySelect.disabled = false;
        }

        if (transferToggle) {
            transferToggle.checked = false;
        }

        // Reset file input and previews
        const fileInput = document.getElementById('{{ $prefix }}_attachments');
        if (fileInput) {
            fileInput.value = '';
        }
        this.previews = [];
    }"
    x-on:close-modal.window="if ((! $event.detail?.id || $event.detail.id === '{{ $modalId }}') && !{{ $hasErrors ? 'true' : 'false' }}) { open = false }"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    style="display: none;"
>
    <div
        x-show="open"
        @click.outside="if (!{{ $hasErrors ? 'true' : 'false' }}) { open = false }"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="mx-4 w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900"
    >
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $title }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ $description ?? ($accountName
                            ? __('Record a new transaction for :account', ['account' => $accountName])
                            : __('Record a new transaction for :company', ['company' => $companyName ?? __('this company')])) }}
                    </p>
                </div>
                <button
                    @click="if (!{{ $hasErrors ? 'true' : 'false' }}) { open = false }"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300 {{ $hasErrors ? 'cursor-not-allowed opacity-50' : '' }}"
                    @if($hasErrors) disabled @endif
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('transactions.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                {{-- Display Validation Errors --}}
                @if ($errors->any())
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">
                                    {{ __('Please fix the following errors:') }}
                                </h3>
                                <ul class="text-sm text-red-700 dark:text-red-300 space-y-1 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <input type="hidden" name="company_id" value="{{ $companyId }}">

                @if ($accountId)
                    <input type="hidden" name="account_id" value="{{ $accountId }}">
                @endif

                @if ($redirectInput)
                    <input type="hidden" name="{{ $redirectInput }}" value="1">
                @endif

                <div class="grid gap-4 sm:grid-cols-2">

                    @if (! empty($accountOptions))
                        <div class="sm:col-span-2">
                            <x-forms.select
                                label="{{ __('Account') }}"
                                name="account_id"
                                id="{{ $accountSelectId }}"
                                :options="$accountOptions"
                                placeholder="{{ __('Select account') }}"
                                class="w-full"
                                required
                                x-on:change="
                                    const relatedSelect = document.getElementById('{{ $relatedSelectId }}');
                                    if (! relatedSelect) {
                                        return;
                                    }

                                    [...relatedSelect.options].forEach((option) => {
                                        if (! option.value) {
                                            return;
                                        }

                                        option.disabled = option.value === $el.value;

                                        if (option.disabled && option.selected) {
                                            relatedSelect.value = '';
                                        }
                                    });
                                "
                            />
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <div class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                id="{{ $transferToggleId }}"
                                name="is_transfer"
                                value="1"
                                x-on:change="
                                    const isTransfer = $el.checked;
                                    const categorySelect = document.getElementById('{{ $categorySelectId }}');
                                    const relatedSelect = document.getElementById('{{ $relatedSelectId }}');

                                    if (categorySelect) {
                                        categorySelect.disabled = isTransfer;
                                        if (isTransfer) {
                                            categorySelect.value = '';
                                        }
                                    }

                                    if (relatedSelect) {
                                        relatedSelect.disabled = ! isTransfer;
                                        if (! isTransfer) {
                                            relatedSelect.value = '';
                                        }
                                    }
                                "
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="{{ $transferToggleId }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Is Transfer?') }}
                            </label>
                        </div>
                    </div>

                    <div>
                        <x-forms.select
                            label="{{ __('Transaction Category') }}"
                            name="transaction_category_id"
                            id="{{ $categorySelectId }}"
                            :options="$categories"
                            placeholder="{{ __('Select category') }}"
                            class="w-full"
                        />
                    </div>
                    <div>
                        <x-forms.select
                            label="{{ __('Client') }}"
                            name="client_id"
                            id="{{ $clientSelectId }}"
                            :options="$clients"
                            placeholder="{{ __('Select client') }}"
                            class="w-full"
                        />
                    </div>

                    <div>
                        <x-forms.input
                            label="{{ __('Transaction ID (optional)') }}"
                            name="transaction_id"
                            type="text"
                            placeholder="{{ __('Enter external reference') }}"
                        />
                    </div>
                    <div>
                        <x-forms.select
                            label="{{ __('Related Account (for transfer)') }}"
                            name="related_account_id"
                            id="{{ $relatedSelectId }}"
                            :options="$transferAccounts"
                            placeholder="{{ __('Select related account') }}"
                            class="w-full"
                            disabled
                        />
                    </div>

                    <div>
                        <x-forms.input
                            label="{{ __('Amount') }}"
                            name="amount"
                            type="number"
                            step="0.01"
                            placeholder="{{ __('Enter amount') }}"
                            required
                        />
                    </div>

                    <div>
                        <x-forms.input
                            label="{{ __('Date') }}"
                            name="date"
                            type="date"
                            :value="now()->format('Y-m-d')"
                            required
                        />
                    </div>

                    <div>
                        <x-forms.select
                            label="{{ __('Status') }}"
                            name="status"
                            :options="$statuses"
                            placeholder="{{ __('Select status') }}"
                            class="w-full"
                        />
                    </div>
                </div>

                {{-- Description and Attachments Side by Side --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-forms.textarea
                            label="{{ __('Description') }}"
                            name="description"
                            placeholder="{{ __('Enter description (optional)') }}"
                            rows="4"
                        />
                    </div>

                    {{-- File Attachments Section --}}
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Attachments') }} <span class="text-gray-500 dark:text-gray-400 text-xs">({{ __('Optional') }})</span>
                            </label>
                            <input
                                type="file"
                                name="attachments[]"
                                id="{{ $prefix }}_attachments"
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
                                {{ __('Allowed: Images (JPG, PNG, GIF, WEBP), Max 10MB per file.') }}
                            </p>
                        </div>

                        {{-- Image Previews --}}
                        <div x-show="previews.length > 0" class="space-y-2">
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Selected Files:') }}
                            </p>
                            <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                                <template x-for="(preview, index) in previews" :key="index">
                                    <div class="relative border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                        <template x-if="preview.url">
                                            <div class="relative">
                                                <img :src="preview.url" :alt="preview.name" class="w-full h-20 object-cover">
                                                <button
                                                    type="button"
                                                    @click="removePreview(index)"
                                                    class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-0.5 hover:bg-red-700 transition-colors"
                                                >
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="!preview.url">
                                            <div class="p-2 bg-gray-50 dark:bg-gray-800 text-center">
                                                <svg class="w-6 h-6 mx-auto text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <div class="p-1.5 bg-white dark:bg-gray-900">
                                            <p class="text-xs text-gray-600 dark:text-gray-400 truncate" :title="preview.name" x-text="preview.name"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        @click="if (!{{ $hasErrors ? 'true' : 'false' }}) { open = false }"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 {{ $hasErrors ? 'cursor-not-allowed opacity-50' : '' }}"
                        @if($hasErrors) disabled @endif
                    >
                        {{ __('Cancel') }}
                    </button>
                    <x-button type="submit">
                        {{ __('Save Transaction') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>

