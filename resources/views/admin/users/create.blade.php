<x-layouts.app>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Add New User') }}</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                {{ __('Create a new user') }}
            </p>
        </div>

        <a href="{{ route('users.index') }}"
            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
            ← {{ __('Back to users') }}
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 ">
        <form method="POST" action="{{ route('users.store') }}"
            class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
            @csrf

            <!-- First Column -->
            <div class="space-y-4">
                <x-forms.input label="Name" name="name" placeholder="Enter name" class="w-full" />

                @if(auth()->user()->hasRole('super-admin') && !empty($companies))
                    <x-forms.select label="Company" name="company_id" :options="$companies" placeholder="Select company"
                        class="w-full" />
                @endif

                <x-forms.select label="Role" name="role" :options="$roles" placeholder="Select role"
                    class="w-full" />
            </div>
            <div class="space-y-4">
                <x-forms.input label="Email" name="email" type="email" placeholder="Enter email address"
                    class="w-full" />
                <x-forms.input label="Password" name="password" type="password" placeholder="Enter password"
                    class="w-full" />
                <x-forms.input label="Confirm Password" name="password_confirmation" type="password"
                    placeholder="Confirm password" class="w-full" />
            </div>
            <!-- Full width button row -->
            <div class="md:col-span-2 flex justify-end mt-4">
                <x-button>
                    Save User
                </x-button>
            </div>
        </form>
    </div>

</x-layouts.app>
