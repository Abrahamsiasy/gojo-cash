<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Add New Company') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('Create a componey') }}
        </p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <form method="POST" action="{{ route('companies.store') }}">
            @csrf

            <x-forms.input 
                label="Company Name" 
                name="name" 
                placeholder="Enter company name" 
                class="mb-4"
            />

            <x-forms.input 
                label="Trial Ends At" 
                name="trial_ends_at" 
                type="date" 
                class="mb-4"
            />

            <x-forms.checkbox 
                label="Active" 
                name="status" 
                value="1" 
                class="mb-4"
                checked
            />

            <div class="flex justify-end mt-4">
                <x-button>
                    Save Company
                </x-button>
            </div>
        </form>

        </div>

</x-layouts.app>
