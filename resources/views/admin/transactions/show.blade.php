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

        {{-- Attachments Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Attachments') }}
                    </h2>
                    <button
                        @click="$dispatch('open-modal', { id: 'upload-attachment-{{ $transaction->id }}' })"
                        class="text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition-colors"
                    >
                        {{ __('Upload Files') }}
                    </button>
                </div>

                @if($transaction->attachments->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                        {{ __('No attachments uploaded yet.') }}
                    </p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($transaction->attachments as $attachment)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" title="{{ $attachment->original_name }}">
                                            {{ $attachment->original_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $attachment->human_readable_size }} • {{ $attachment->created_at->format('M j, Y') }}
                                        </p>
                                    </div>
                                </div>
                                @if($attachment->isImage())
                                    <div class="mb-3">
                                        <img
                                            src="{{ $attachment->url }}"
                                            alt="{{ $attachment->original_name }}"
                                            class="w-full h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer"
                                            @click="$dispatch('open-modal', { id: 'view-image-{{ $attachment->id }}' })"
                                        >
                                    </div>
                                @endif
                                <div class="flex items-center gap-2 mt-3">
                                    @if($attachment->isImage())
                                        <button
                                            type="button"
                                            @click="$dispatch('open-modal', { id: 'view-image-{{ $attachment->id }}' })"
                                            class="flex-1 text-center text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium px-3 py-1.5 rounded transition-colors"
                                        >
                                            {{ __('View Image') }}
                                        </button>
                                    @else
                                        <a
                                            href="{{ $attachment->url }}"
                                            target="_blank"
                                            class="flex-1 text-center text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium px-3 py-1.5 rounded transition-colors"
                                        >
                                            {{ __('View File') }}
                                        </a>
                                    @endif
                                    <form
                                        method="POST"
                                        action="{{ route('transactions.attachments.destroy', [$transaction, $attachment]) }}"
                                        x-data
                                        x-on:modal-confirm.window="if ($event.detail?.id === 'delete-attachment-{{ $attachment->id }}') { $el.submit() }"
                                        class="flex-1"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="button"
                                            @click="$dispatch('open-modal', { id: 'delete-attachment-{{ $attachment->id }}' })"
                                            class="w-full text-center text-sm bg-red-600 hover:bg-red-700 text-white font-medium px-3 py-1.5 rounded transition-colors"
                                        >
                                            {{ __('Delete') }}
                                        </button>
                                        <x-modal
                                            id="delete-attachment-{{ $attachment->id }}"
                                            title="{{ __('Delete Attachment') }}"
                                            confirmText="{{ __('Delete') }}"
                                            cancelText="{{ __('Cancel') }}"
                                            confirmColor="red"
                                        >
                                            {{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $attachment->original_name]) }}
                                        </x-modal>
                                    </form>

                                    {{-- Image Viewer Modal --}}
                                    @if($attachment->isImage())
                                        <div
                                            x-data="{ open: false }"
                                            x-show="open"
                                            x-cloak
                                            x-on:open-modal.window="if ($event.detail?.id === 'view-image-{{ $attachment->id }}') { open = true }"
                                            x-on:close-modal.window="if (! $event.detail?.id || $event.detail.id === 'view-image-{{ $attachment->id }}') { open = false }"
                                            x-on:keydown.escape.window="open = false"
                                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm"
                                        >
                                            <div
                                                x-show="open"
                                                @click.outside="open = false"
                                                x-transition.scale.duration.150ms
                                                class="relative w-full h-full flex items-center justify-center p-4"
                                            >
                                                <button
                                                    @click="open = false"
                                                    class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors z-10"
                                                >
                                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                                <img
                                                    src="{{ $attachment->url }}"
                                                    alt="{{ $attachment->original_name }}"
                                                    class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
                                                >
                                                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/50 text-white px-4 py-2 rounded-lg text-sm">
                                                    {{ $attachment->original_name }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Upload Modal --}}
                <div
                    x-data="{
                        open: false,
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
                                            url: e.target.result,
                                            file: file
                                        });
                                    };
                                    reader.readAsDataURL(file);
                                } else {
                                    this.previews.push({
                                        name: file.name,
                                        size: file.size,
                                        url: null,
                                        file: file
                                    });
                                }
                            });
                        },
                        removePreview(index) {
                            this.previews.splice(index, 1);
                            // Note: We can't directly remove files from input, so we'll rely on form validation
                            // The user can re-select files if needed
                        }
                    }"
                    x-show="open"
                    x-cloak
                    x-on:open-modal.window="if ($event.detail?.id === 'upload-attachment-{{ $transaction->id }}') { open = true; previews = []; }"
                    x-on:close-modal.window="if (! $event.detail?.id || $event.detail.id === 'upload-attachment-{{ $transaction->id }}') { open = false; previews = []; }"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                >
                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition.scale.duration.150ms
                        class="mx-4 w-full max-w-2xl rounded-2xl border border-gray-200 bg-white dark:bg-gray-900 p-6 shadow-xl max-h-[90vh] overflow-y-auto"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            {{ __('Upload Attachments') }}
                        </h3>
                        <form
                            method="POST"
                            action="{{ route('transactions.attachments.store', $transaction) }}"
                            enctype="multipart/form-data"
                            class="space-y-4"
                        >
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('Select Files') }}
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
                                    required
                                >
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Allowed: Images (JPG, PNG, GIF, WEBP), Documents (PDF, DOC, DOCX, XLS, XLSX, CSV). Max 10MB per file.') }}
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
                            @if($errors->any())
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                    <ul class="text-sm text-red-600 dark:text-red-400 space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button
                                    type="button"
                                    @click="open = false"
                                    class="rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 transition hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    {{ __('Cancel') }}
                                </button>
                                <button
                                    type="submit"
                                    class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                                >
                                    {{ __('Upload') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
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

