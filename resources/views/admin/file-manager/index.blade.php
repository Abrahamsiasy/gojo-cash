<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('File Manager') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('View and manage transactions with file attachments.') }}
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
            :action="route('file-manager.index')"
            :value="$search"
            :placeholder="__('Search transactions with files...')"
        />
    </div>

    <x-table
        :headers="$headers"
        :rows="$rows"
        :actions="['view' => true, 'edit' => false, 'delete' => false]"
        :paginator="$transactions"
    />
</x-layouts.app>

