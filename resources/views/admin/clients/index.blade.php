<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Clients') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('View and manage all Clients') }}
        </p>
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-table.search
            class="w-full sm:w-auto"
            :action="route('clients.index')"
            :value="$search"
            :placeholder="__('Search clients...')"
        />

        <x-button tag="a" href="{{ route('clients.create') }}" class="w-full sm:w-auto px-4 py-2 text-sm">
            {{ __('Create Client') }}
        </x-button>
    </div>

    <x-table
        :headers="$headers"
        :rows="$rows"
        :actions="['view' => true, 'edit' => true, 'delete' => true]"
        :paginator="$clients"
        :model="$model"
    />
</x-layouts.app>
