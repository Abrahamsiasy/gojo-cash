<x-layouts.app>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    {{ __('Invoice Template Details') }}
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('View and manage invoice template settings') }}
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('invoice-templates.preview', $template) }}"
                    class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50">
                    {{ __('Preview') }}
                </a>
                <a href="{{ route('invoice-templates.index') }}"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    ← {{ __('Back to templates') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Basic Information --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Basic Information') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Template Name') }}
                            </label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100 font-semibold">
                                {{ $template->name }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Type') }}
                            </label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ ucfirst(str_replace('_', ' ', $template->type)) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Company') }}
                            </label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                <a href="{{ route('companies.show', $template->company) }}"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $template->company->name }}
                                </a>
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Default Template') }}
                            </label>
                            <p class="mt-1">
                                @if($template->is_default)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        {{ __('Yes') }}
                                    </span>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('No') }}</span>
                                @endif
                            </p>
                        </div>

                        @if($template->description)
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Description') }}
                                </label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    {{ $template->description }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Branding Assets --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Branding Assets') }}
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @if($template->logo_path)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2 block">
                                    {{ __('Logo') }}
                                </label>
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($template->logo_path) }}" alt="Logo"
                                    class="w-full h-24 object-contain border border-gray-200 dark:border-gray-700 rounded">
                            </div>
                        @endif

                        @if($template->stamp_path)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2 block">
                                    {{ __('Stamp') }}
                                </label>
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($template->stamp_path) }}" alt="Stamp"
                                    class="w-full h-24 object-contain border border-gray-200 dark:border-gray-700 rounded">
                            </div>
                        @endif

                        @if($template->watermark_path)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2 block">
                                    {{ __('Watermark') }}
                                </label>
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($template->watermark_path) }}" alt="Watermark"
                                    class="w-full h-24 object-contain border border-gray-200 dark:border-gray-700 rounded">
                            </div>
                        @endif

                        @if($template->signature_path)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2 block">
                                    {{ __('Signature') }}
                                </label>
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($template->signature_path) }}" alt="Signature"
                                    class="w-full h-24 object-contain border border-gray-200 dark:border-gray-700 rounded">
                            </div>
                        @endif

                        @if(!$template->logo_path && !$template->stamp_path && !$template->watermark_path && !$template->signature_path)
                            <p class="col-span-4 text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                {{ __('No branding assets uploaded.') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Settings --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Settings') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ __('Show QR Code') }}
                            </label>
                            <p class="mt-1">
                                @if($template->show_qr_code)
                                    <span class="text-green-600 dark:text-green-400">{{ __('Yes') }}</span>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('No') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar Actions --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Actions') }}
                    </h2>
                    <div class="space-y-3">
                        <x-button tag="a" href="{{ route('invoice-templates.edit', $template) }}" class="w-full justify-center">
                            {{ __('Edit Template') }}
                        </x-button>

                        <a href="{{ route('invoice-templates.preview', $template) }}"
                            class="block w-full text-center px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50">
                            {{ __('Preview Template') }}
                        </a>

                        <form method="POST" action="{{ route('invoice-templates.destroy', $template) }}"
                            x-data
                            x-on:modal-confirm.window="if ($event.detail?.id === 'delete-template-{{ $template->id }}') { $el.submit() }">
                            @csrf
                            @method('DELETE')

                            <x-button type="danger" buttonType="button" class="w-full justify-center"
                                @click="$dispatch('open-modal', { id: 'delete-template-{{ $template->id }}' })">
                                {{ __('Delete Template') }}
                            </x-button>

                            <x-modal id="delete-template-{{ $template->id }}" title="{{ __('Delete Template') }}"
                                confirmText="{{ __('Delete') }}" cancelText="{{ __('Cancel') }}" confirmColor="red">
                                {{ __('Are you sure you want to delete this template? This action cannot be undone.') }}
                            </x-modal>
                        </form>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Metadata') }}
                    </h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('Created') }}</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $template->created_at->format('M j, Y') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('Updated') }}</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $template->updated_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

