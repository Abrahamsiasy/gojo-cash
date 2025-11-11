<x-layouts.app>


    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Add New Account') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('Create a new financial account for tracking balances.') }}
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 ">
        <form method="POST" action="{{ route('accounts.store') }}" class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
            @csrf
            
            <!-- First Column -->
            <div class="space-y-4">
                <x-forms.input label="Account Name" name="name" placeholder="Enter account name" class="w-full" />
                
                <x-forms.select label="Company" name="company_id" :options="$companies" placeholder="Select company" class="w-full" />
                
                <x-forms.input label="Account Number" name="account_number" type="text" placeholder="Enter account number" class="w-full" />
                
                <x-forms.select label="Account Type" name="account_type" :options="$accountTypeOptions" placeholder="Select account type" class="w-full" />
            </div>
            
            <!-- Second Column -->
            <div class="space-y-4">
                <x-forms.input label="Bank Name" name="bank_name" type="text" placeholder="Enter bank name" class="w-full" />
                
                <x-forms.input label="Balance" name="balance" type="number" placeholder="Enter balance" class="w-full" />
                
                <x-forms.input label="Opening Balance" name="opening_balance" type="number" placeholder="Enter opening balance" class="w-full" />
                
                <x-forms.textarea label="Description" name="description" placeholder="Enter description" class="w-full" />
            </div>
            
            <!-- Full width button row -->
            <div class="md:col-span-2 flex justify-end mt-4">
                <x-button>
                    Save Account
                </x-button>
            </div>
        </form>
    </div>

</x-layouts.app>