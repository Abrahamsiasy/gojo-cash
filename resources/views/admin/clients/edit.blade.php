<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ __('Edit Client') }}
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                {{ __('Update the client details') }}
            </p>
        </div>

        <a href="{{ route('clients.index') }}"
            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
            ← {{ __('Back to clients') }}
        </a>
    </div>
    <div class="grid grid-cols-1 gap-4">
        <form method="POST" action="{{ route('clients.update', $client->id) }}"
            class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
            @csrf
            @method('PUT')

            <!-- First Column -->
            <div class="space-y-4">
                <x-forms.input label="Name" name="name" placeholder="Enter name" class="w-full"
                    value="{{ old('name', $client->name) }}" />
                <x-forms.select label="Company" name="company_id" :options="$companies" placeholder="Select company"
                    class="w-full my-4" selected="{{ old('company_id', $client->company_id) }}" />
            </div>

            <!-- Second Column -->
            <div class="space-y-4">
                <x-forms.input label="Ac" name="email" type="email" placeholder="Enter email address"
                    class="w-full" value="{{ old('email', $client->email) }}" />
                <x-forms.input label="Address" name="address" placeholder="Enter address" class="w-full"
                    value="{{ old('address', $client->address) }}" />
            </div>

            <!-- Full width button row -->
            <div class="md:col-span-2 flex justify-end mt-4 space-x-2">
                <x-button type="submit">
                    Update Client
                </x-button>

                <x-button type="button" tag="a" href="{{ route('clients.index') }}"
                    class="bg-gray-500 hover:bg-gray-600">
                    Cancel
                </x-button>
            </div>
        </form>
    </div>



</x-layouts.app>
