<x-layouts.app>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Add New Transaction Category') }}</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                {{ __('Create a new transaction category for tracking income and expenses.') }}
            </p>
        </div>

        <a href="{{ route('transaction-categories.index') }}"
            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
            ← {{ __('Back to transaction categories') }}
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4">
        <form method="POST" action="{{ route('transaction-categories.store') }}" class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
            @csrf
            
            <!-- First Column -->
            <div class="space-y-4">
                <x-forms.input 
                    label="Category Name" 
                    name="name" 
                    placeholder="Enter category name" 
                    class="w-full" 
                />

                <x-forms.select 
                    label="Company" 
                    name="company_id" 
                    :options="$companies" 
                    placeholder="Select company" 
                    class="w-full" 
                />

                <x-forms.select 
                    label="Type" 
                    name="type" 
                    :options="['income' => 'Income', 'expense' => 'Expense']" 
                    placeholder="Select type" 
                    class="w-full" 
                />
            </div>

            <!-- Second Column -->
            <div class="space-y-4">
                <x-forms.checkbox 
                    label="Default Category" 
                    name="is_default" 
                    :checked="old('is_default', false)" 
                />
                
                <x-forms.textarea 
                    label="Description" 
                    name="description" 
                    placeholder="Enter description" 
                    class="w-full" 
                />
            </div>

            <!-- Full width button row -->
            <div class="md:col-span-2 flex justify-end mt-4">
                <x-button>
                    Save Category
                </x-button>
            </div>
        </form>
    </div>

</x-layouts.app>
