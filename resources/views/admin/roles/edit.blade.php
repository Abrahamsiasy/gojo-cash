<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ __('Edit Role') }}
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                {{ __('Update the role details') }}
            </p>
        </div>

        <a href="{{ route('roles.index') }}"
            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
            ← {{ __('Back to roles') }}
        </a>
    </div>
    <div class="grid grid-cols-1 gap-4">
        <form method="POST" action="{{ route('roles.update', $role->id) }}"
            class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 md:gap-4">
            @csrf
            @method('PUT')

            <!-- First Column -->
            <div class="space-y-4">
                <x-forms.input label="Name" name="name" placeholder="Enter name" class="w-full"
                    value="{{ old('name', $role->name) }}" />
                <div class="flex items-center">
                    <input type="checkbox" name="check_all" id="check_all"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        onclick="toggleAllPermissions(this)">
                    <label for="check_all" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                        Check All
                    </label>
                </div>
                <div class="mt-2">
                    <p>Permissions</p>
                </div>
                @foreach ($permissions as $permission)
                    <div class="flex items-center">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                            id="permission_{{ $permission->id }}"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                        <label for="permission_{{ $permission->id }}"
                            class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            {{ $permission->name }}
                        </label>
                    </div>
                @endforeach

                <!-- Full width button row -->
                <div class="md:col-span-2 flex justify-end mt-4 space-x-2">
                    <x-button type="submit">
                        Update Role
                    </x-button>

                    <x-button type="button" tag="a" href="{{ route('roles.index') }}"
                        class="bg-gray-500 hover:bg-gray-600">
                        Cancel
                    </x-button>
                </div>
        </form>
    </div>
    @push('scripts')
        <script>
            function toggleAllPermissions() {
                const checkAllBox = document.getElementById('check_all');
                const permissionCheckboxes = document.querySelectorAll('input[name="permissions[]"]');

                permissionCheckboxes.forEach(checkbox => {
                    checkbox.checked = checkAllBox.checked;
                });
            }
        </script>
    @endpush
</x-layouts.app>
