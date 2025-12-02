<x-layouts.app>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    {{ __('Invoice Details') }}
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('View invoice information and download PDF') }}
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('invoices.preview', $invoice) }}" target="_blank"
                    class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50">
                    {{ __('Preview') }}
                </a>
                <a href="{{ route('invoices.download', $invoice) }}"
                    class="px-4 py-2 text-sm font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/50">
                    {{ __('Download PDF') }}
                </a>
                <a href="{{ route('invoices.index') }}"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    ← {{ __('Back to invoices') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Invoice Information --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Invoice Information') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Invoice Number') }}
                            </label>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $invoice->invoice_number }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Invoice Type') }}
                            </label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ ucfirst(str_replace('_', ' ', $invoice->invoice_type)) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Issue Date') }}
                            </label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                {{ $invoice->issue_date->format('F j, Y') }}
                            </p>
                        </div>

                        @if($invoice->due_date)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Due Date') }}
                                </label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    {{ $invoice->due_date->format('F j, Y') }}
                                </p>
                            </div>
                        @endif

                        @if($invoice->reference_number)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Reference Number') }}
                                </label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    {{ $invoice->reference_number }}
                                </p>
                            </div>
                        @endif

                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Total Amount') }}
                            </label>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Customer Information --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Customer Information') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Customer Name') }}
                            </label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                {{ $invoice->customer_name ?? $invoice->client?->name ?? __('—') }}
                            </p>
                        </div>

                        @if($invoice->customer_email)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Email') }}
                                </label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    {{ $invoice->customer_email }}
                                </p>
                            </div>
                        @endif

                        @if($invoice->customer_phone)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Phone') }}
                                </label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    {{ $invoice->customer_phone }}
                                </p>
                            </div>
                        @endif

                        @if($invoice->customer_address)
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Address') }}
                                </label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    {{ $invoice->customer_address }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Invoice Items --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Invoice Items') }}
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        {{ __('Description') }}
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        {{ __('Quantity') }}
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        {{ __('Unit Price') }}
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        {{ __('Total') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $item['description'] ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">
                                            {{ number_format($item['quantity'] ?? 0, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">
                                            {{ number_format($item['unit_price'] ?? 0, 2) }} {{ $invoice->currency }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-gray-100">
                                            {{ number_format($item['total'] ?? (($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)), 2) }} {{ $invoice->currency }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 text-right">
                                        {{ __('Subtotal') }}:
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100 text-right">
                                        {{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}
                                    </td>
                                </tr>
                                @if($invoice->tax_amount > 0)
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 text-right">
                                            {{ __('Tax') }} ({{ $invoice->tax_rate ?? 0 }}%):
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100 text-right">
                                            {{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}
                                        </td>
                                    </tr>
                                @endif
                                @if($invoice->discount_amount > 0)
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 text-right">
                                            {{ __('Discount') }}:
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-red-600 dark:text-red-400 text-right">
                                            -{{ number_format($invoice->discount_amount, 2) }} {{ $invoice->currency }}
                                        </td>
                                    </tr>
                                @endif
                                <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                                    <td colspan="3" class="px-4 py-3 text-lg font-bold text-gray-900 dark:text-gray-100 text-right">
                                        {{ __('Total') }}:
                                    </td>
                                    <td class="px-4 py-3 text-lg font-bold text-gray-900 dark:text-gray-100 text-right">
                                        {{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                @if($invoice->terms_and_conditions || $invoice->bank_details || $invoice->notes)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            {{ __('Additional Information') }}
                        </h2>
                        <div class="space-y-4">
                            @if($invoice->terms_and_conditions)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('Terms and Conditions') }}
                                    </label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
                                        {{ $invoice->terms_and_conditions }}
                                    </p>
                                </div>
                            @endif

                            @if($invoice->bank_details)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('Bank Details') }}
                                    </label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
                                        {{ $invoice->bank_details }}
                                    </p>
                                </div>
                            @endif

                            @if($invoice->notes)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('Notes') }}
                                    </label>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
                                        {{ $invoice->notes }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Actions') }}
                    </h2>
                    <div class="space-y-3">
                        <a href="{{ route('invoices.preview', $invoice) }}" target="_blank"
                            class="block w-full text-center px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50">
                            {{ __('Preview Invoice') }}
                        </a>

                        <a href="{{ route('invoices.download', $invoice) }}"
                            class="block w-full text-center px-4 py-2 text-sm font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/50">
                            {{ __('Download PDF') }}
                        </a>

                        <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
                            class="block w-full text-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900/50">
                            {{ __('Print Invoice') }}
                        </a>

                        <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                            x-data
                            x-on:modal-confirm.window="if ($event.detail?.id === 'delete-invoice-{{ $invoice->id }}') { $el.submit() }">
                            @csrf
                            @method('DELETE')

                            <x-button type="danger" buttonType="button" class="w-full justify-center"
                                @click="$dispatch('open-modal', { id: 'delete-invoice-{{ $invoice->id }}' })">
                                {{ __('Delete Invoice') }}
                            </x-button>

                            <x-modal id="delete-invoice-{{ $invoice->id }}" title="{{ __('Delete Invoice') }}"
                                confirmText="{{ __('Delete') }}" cancelText="{{ __('Cancel') }}" confirmColor="red">
                                {{ __('Are you sure you want to delete this invoice? This action cannot be undone.') }}
                            </x-modal>
                        </form>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Template Information') }}
                    </h2>
                    <div class="space-y-2 text-sm">
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">{{ __('Template') }}</label>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $invoice->template->name }}
                            </p>
                        </div>
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">{{ __('Type') }}</label>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ ucfirst(str_replace('_', ' ', $invoice->template->type)) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Metadata') }}
                    </h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('Created') }}</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $invoice->created_at->format('M j, Y') }}
                            </span>
                        </div>
                        @if($invoice->creator)
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">{{ __('Created By') }}</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $invoice->creator->name }}
                                </span>
                            </div>
                        @endif
                        @if($invoice->transaction)
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">{{ __('Related Transaction') }}</label>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('transactions.show', $invoice->transaction) }}"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        TXN-{{ str_pad($invoice->transaction->id, 5, '0', STR_PAD_LEFT) }}
                                    </a>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

