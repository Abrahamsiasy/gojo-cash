{{-- <x-layouts.app>
    <div class="">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Companies') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('View componys linked to your expense management system.') }}
        </p>
    </div>

</x-layouts.app> --}}

<x-layouts.app>
    <div class="mb-6 bg-white px-4">
        {{-- Header --}}
        <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">
                {{ __('Company Details') }}
            </h1>
            <a href="{{ route('companies.index') }}"
                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                ← Back to List
            </a>
        </div>

        {{-- Company Info --}}
        <div class="space-y-4">
            <div>
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</h2>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $company->name }}</p>
            </div>

            <div>
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Slug</h2>
                <p class="text-gray-900 dark:text-gray-100">{{ $company->slug }}</p>
            </div>

            <div>
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h2>
                @if($company->status)
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">
                        Active
                    </span>
                @else
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">
                        Inactive
                    </span>
                @endif
            </div>

            <div>
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Trial Ends At</h2>
                <p class="text-gray-900 dark:text-gray-100">
                    {{ $company->trial_ends_at ? $company->trial_ends_at->format('F j, Y') : '—' }}
                </p>
            </div>

            <div>
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</h2>
                <p class="text-gray-900 dark:text-gray-100">
                    {{ $company->created_at->format('F j, Y, g:i A') }}
                </p>
            </div>

            <div>
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</h2>
                <p class="text-gray-900 dark:text-gray-100">
                    {{ $company->updated_at->format('F j, Y, g:i A') }}
                </p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex justify-end items-center gap-3 mt-8">
            <a href="{{ route('companies.edit', $company) }}"
            class="px-4 py-2 border border-blue-600 text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 rounded-lg font-medium text-sm transition">
             Edit
         </a>
         


            <form action="{{ route('companies.destroy', $company) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this company?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium text-sm transition">
                    Delete
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>

