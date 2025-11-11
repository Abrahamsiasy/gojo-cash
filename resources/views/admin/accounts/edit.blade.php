<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            {{ __('Edit accounts') }}
        </h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">
            {{ __('Update the accounts details and status.') }}
        </p>
    </div>
    <div class="grid grid-cols-1 gap-4">
        <form method="POST" action="{{ route('accounts.update', $account->id) }}" class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
            @csrf
            @method('PUT')
            
            <!-- First Column -->
            <div class="space-y-4">
                <x-forms.input 
                    label="Account Name" 
                    name="name" 
                    placeholder="Enter account name" 
                    class="w-full" 
                    value="{{ old('name', $account->name) }}" 
                />
    
                <x-forms.select 
                    label="Company" 
                    name="company_id" 
                    :options="$companies" 
                    placeholder="Select company" 
                    class="w-full my-4" 
                    selected="{{ old('company_id', $account->company_id) }}" 
                />
    
                <x-forms.input 
                    label="Account Number" 
                    name="account_number" 
                    type="text" 
                    placeholder="Enter account number" 
                    class="w-full" 
                    value="{{ old('account_number', $account->account_number) }}" 
                />
    
                <x-forms.select 
                    label="Account Type" 
                    name="account_type" 
                    :options="$accountTypeOptions" 
                    placeholder="Select account type" 
                    class="w-full" 
                    selected="{{ old('account_type', $account->account_type) }}" 
                />
            </div>
            
            <!-- Second Column -->
            <div class="space-y-4">
                <x-forms.input 
                    label="Bank Name" 
                    name="bank_name" 
                    type="text" 
                    placeholder="Enter bank name" 
                    class="w-full" 
                    value="{{ old('bank_name', $account->bank_name) }}" 
                />
    
                <x-forms.input 
                    label="Balance" 
                    name="balance" 
                    type="number" 
                    placeholder="Enter balance" 
                    class="w-full" 
                    value="{{ old('balance', $account->balance) }}" 
                />
    
                <x-forms.input 
                    label="Opening Balance" 
                    name="opening_balance" 
                    type="number" 
                    placeholder="Enter opening balance" 
                    class="w-full" 
                    value="{{ old('opening_balance', $account->opening_balance) }}" 
                />
    
                <x-forms.textarea 
                    label="Description" 
                    name="description" 
                    placeholder="Enter description" 
                    class="w-full"
                >{{ old('description', $account->description) }}</x-forms.textarea>
            </div>
            
            <!-- Full width button row -->
            <div class="md:col-span-2 flex justify-end mt-4 space-x-2">
                <x-button type="submit">
                    Update Account
                </x-button>
                
                <x-button type="button" tag="a" href="{{ route('accounts.index') }}" class="bg-gray-500 hover:bg-gray-600">
                    Cancel
                </x-button>
            </div>
        </form>
    </div>
    


</x-layouts.app>