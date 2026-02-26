<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Invoice Templates') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('Manage invoice templates for your company. Create and customize templates for different invoice types.') }}
        </p>
    </div>

    @if (session('success'))
        <x-alert type="success" :message="session('success')" />
    @endif

    @if (session('error'))
        <x-alert type="danger" :message="session('error')" />
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-table.search
            class="w-full sm:w-auto"
            :action="route('invoice-templates.index')"
            :value="$search"
            :placeholder="__('Search templates...')"
        />

        @can('create invoicetemplate')
            <x-button tag="a" href="{{ route('invoice-templates.create') }}" class="w-full sm:w-auto px-4 py-2 text-sm">
                {{ __('Create Template') }}
            </x-button>
        @endcan
    </div>

    <x-table
        :headers="$headers"
        :rows="$rows"
        :actions="['view' => true, 'edit' => true, 'delete' => true]"
        :paginator="$templates"
    />
</x-layouts.app>

