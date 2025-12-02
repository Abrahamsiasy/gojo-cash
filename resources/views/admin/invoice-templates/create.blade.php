<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Create Invoice Template') }}</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                {{ __('Create a new invoice template with custom branding and styling.') }}
            </p>
        </div>

        <a href="{{ route('invoice-templates.index') }}"
            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
            ← {{ __('Back to templates') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
            <ul class="text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('invoice-templates.store') }}" enctype="multipart/form-data"
        class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Basic Information') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-forms.select label="Company" name="company_id" :options="$companies" placeholder="Select company" required />

                <x-forms.input label="Template Name" name="name" placeholder="e.g., Standard Invoice" required />

                <x-forms.select label="Template Type" name="type" :options="[
                    'standard' => 'Standard Invoice',
                    'proforma' => 'Proforma Invoice',
                    'credit_note' => 'Credit Note',
                    'recurring' => 'Recurring Invoice',
                    'progress' => 'Progress/Stage Invoice',
                ]" placeholder="Select type" required />

                <x-forms.textarea label="Description" name="description" placeholder="Template description (optional)" />

                <div class="flex items-center">
                    <x-forms.checkbox label="Set as Default Template" name="is_default" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Branding Assets') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Company Logo') }} <span class="text-gray-500 text-xs">(Optional)</span>
                    </label>
                    <input type="file" name="logo" accept="image/*"
                        class="block w-full text-sm text-gray-500 dark:text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-lg file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100
                            dark:file:bg-blue-900/30 dark:file:text-blue-400">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, GIF, WEBP. Max 5MB</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Company Stamp') }} <span class="text-gray-500 text-xs">(Optional)</span>
                    </label>
                    <input type="file" name="stamp" accept="image/*"
                        class="block w-full text-sm text-gray-500 dark:text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-lg file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100
                            dark:file:bg-blue-900/30 dark:file:text-blue-400">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, GIF, WEBP. Max 5MB</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Watermark') }} <span class="text-gray-500 text-xs">(Optional)</span>
                    </label>
                    <input type="file" name="watermark" accept="image/*"
                        class="block w-full text-sm text-gray-500 dark:text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-lg file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100
                            dark:file:bg-blue-900/30 dark:file:text-blue-400">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, GIF, WEBP. Max 5MB</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Signature') }} <span class="text-gray-500 text-xs">(Optional)</span>
                    </label>
                    <input type="file" name="signature" accept="image/*,.pdf"
                        class="block w-full text-sm text-gray-500 dark:text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-lg file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100
                            dark:file:bg-blue-900/30 dark:file:text-blue-400">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, GIF, WEBP, PDF. Max 5MB</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Company Details') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ __('Override default company information for this template (optional)') }}
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-forms.input label="Company Name" name="company_name" placeholder="Company name" />
                <x-forms.input label="Company Phone" name="company_phone" placeholder="Phone number" />
                <x-forms.input label="Company Email" name="company_email" type="email" placeholder="Email address" />
                <x-forms.input label="Company Address" name="company_address" placeholder="Full address" />
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Settings') }}</h2>
            <div class="flex items-center">
                <x-forms.checkbox label="Show QR Code" name="show_qr_code" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('invoice-templates.index') }}"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                {{ __('Cancel') }}
            </a>
            <x-button type="submit">
                {{ __('Create Template') }}
            </x-button>
        </div>
    </form>
</x-layouts.app>

