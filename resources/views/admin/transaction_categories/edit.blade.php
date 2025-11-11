<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            {{ __('Edit Transaction Category') }}
        </h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">
            {{ __('Update the transaction category details.') }}
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4">
        <form method="POST" action="{{ route('transaction-categories.update', $transactionCategory->id) }}" class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
            @csrf
            @method('PUT')
            
            <!-- First Column -->
            <div class="space-y-4">
                <x-forms.input 
                    label="Category Name" 
                    name="name" 
                    placeholder="Enter category name" 
                    class="w-full" 
                    value="{{ old('name', $transactionCategory->name) }}" 
                />

                <x-forms.select 
                    label="Company" 
                    name="company_id" 
                    :options="$companies" 
                    placeholder="Select company" 
                    class="w-full my-4" 
                    selected="{{ old('company_id', $transactionCategory->company_id) }}" 
                />

                <x-forms.select 
                    label="Type" 
                    name="type" 
                    :options="$typeOptions" 
                    placeholder="Select type" 
                    class="w-full" 
                    selected="{{ old('type', $transactionCategory->type) }}" 
                />
            </div>

            <!-- Second Column -->
            <div class="space-y-4">
                <x-forms.checkbox 
                    label="Default Category" 
                    name="is_default" 
                    :checked="old('is_default', $transactionCategory->is_default)" 
                />

                <x-forms.textarea 
                    label="Description" 
                    name="description" 
                    placeholder="Enter description" 
                    class="w-full"
                >{{ old('description', $transactionCategory->description) }}</x-forms.textarea>
            </div>

            <!-- Full width button row -->
            <div class="md:col-span-2 flex justify-end mt-4 space-x-2">
                <x-button type="submit">
                    {{ __('Update Category') }}
                </x-button>

                <x-button type="button" tag="a" href="{{ route('transaction-categories.index') }}" class="bg-gray-500 hover:bg-gray-600">
                    {{ __('Cancel') }}
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
