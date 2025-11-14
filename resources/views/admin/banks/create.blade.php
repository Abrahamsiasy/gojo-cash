<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Add New Bank') }}</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                {{ __('Create a bank') }}
            </p>
        </div>
        <a href="{{ route('banks.index') }}"
            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
            ← {{ __('Back to banks') }}
        </a>
    </div>
    <div class="md:grid-cols-2 gap-4">
        <form method="POST" action="{{ route('banks.store') }}">
            @csrf

            <x-forms.input label="Bank Name" name="name" placeholder="Enter bank name" class="mb-4" />
            <x-forms.textarea label="Description" name="description" placeholder="Enter description (optional)"
                class="w-full" />

            <div class="flex flex-col gap-3">
                <x-forms.checkbox label="Active" name="status" value="1" class="" checked />
                <x-forms.checkbox label="Default" name="is_default" value="1" />
            </div>
            <div class="flex justify-end mt-4">
                <x-button>
                    Save Bank
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
