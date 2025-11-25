<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ __('Edit Bank') }}
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                {{ __('Update the bank details and status.') }}
            </p>
        </div>

        <a href="{{ route('banks.index') }}"
            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
            ← {{ __('Back to banks') }}
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <form method="POST" action="{{ route('banks.update', $bank) }}"
            class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <x-forms.input label="{{ __('Bank Name') }}" name="name" placeholder="{{ __('Enter bank name') }}"
                    :value="old('name', $bank->name)" />
                <x-forms.textarea label="Description" name="description" placeholder="Enter description (optional)"
                    class="w-full" :value="old('name', $bank->description)" />
                <x-forms.checkbox label="{{ __('Default') }}" name="is_default" value="0" :checked="old('is_default', $bank->is_default)"
                    class="mt-1" />

                <x-forms.checkbox label="{{ __('Active') }}" name="status" value="1" :checked="old('status', $bank->status)"
                    class="mt-1" />
            </div>

            <div class="mt-8 flex items-center justify-end gap-3">
                <x-button tag="a" href="{{ route('banks.index') }}" class="px-4 py-2 text-sm">
                    {{ __('Cancel') }}
                </x-button>

                <x-button class="px-4 py-2 text-sm">
                    {{ __('Update Bank') }}
                </x-button>
            </div>
        </form>

        <section
            class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('Bank Snapshot') }}
            </h2>

            <dl class="mt-4 space-y-4 text-sm text-gray-600 dark:text-gray-300">
                <div class="flex items-center justify-between">
                    <dt>{{ __('Status') }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $bank->status ? __('Active') : __('Inactive') }}
                    </dd>
                </div>

                <div class="flex items-center justify-between">
                    <dt>{{ __('Created') }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                        {{ optional($bank->created_at)?->translatedFormat('M j, Y') }}
                    </dd>
                </div>

                <div class="flex items-center justify-between">
                    <dt>{{ __('Last Updated') }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                        {{ optional($bank->updated_at)?->diffForHumans() }}
                    </dd>
                </div>
            </dl>
        </section>
    </div>
</x-layouts.app>
