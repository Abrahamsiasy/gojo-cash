<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Transaction Categories') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('View and manage all transaction categories linked to your companies.') }}
        </p>
    </div>

    @if (session('success'))
        <x-alert type="success" :message="session('success')" />
    @endif

    @if (session('error'))
        <x-alert type="danger" :message="session('error')" />
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-table.search class="w-full sm:w-auto" :action="route('transaction-categories.index')" :value="$search"
            :placeholder="__('Search transaction categories...')" />

        <x-button tag="a" href="{{ route('transaction-categories.create') }}"
            class="w-full sm:w-auto px-4 py-2 text-sm">
            {{ __('Create Category') }}
        </x-button>
    </div>

    <x-table :headers="$headers" :rows="$rows" :actions="['view' => true, 'edit' => true, 'delete' => true]"
        :paginator="$transactionCategories" />
</x-layouts.app>