<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install - Database Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="mb-4">
            <div class="flex items-center justify-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold">1</div>
                <div class="h-1 w-12 bg-gray-300"></div>
                <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">2</div>
                <div class="h-1 w-12 bg-gray-300"></div>
                <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">3</div>
                <div class="h-1 w-12 bg-gray-300"></div>
                <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">4</div>
            </div>
            <div class="flex justify-between text-xs text-gray-600">
                <span>Database</span>
                <span>Migrate</span>
                <span>Admin</span>
                <span>Company</span>
            </div>
        </div>
        <h1 class="text-2xl font-bold mb-6 text-center">Installation - Step 1</h1>
        <h2 class="text-xl mb-4 text-center">Database Configuration</h2>
        
        @if (session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded mb-4" role="alert">
                <strong class="font-bold">Info:</strong>
                <span class="block sm:inline">{{ session('info') }}</span>
            </div>
        @endif

        @if (isset($is_sqlite_configured) && $is_sqlite_configured)
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4">
                <p class="text-sm font-semibold mb-1">✓ SQLite is already configured and ready!</p>
                <p class="text-xs">You can continue with SQLite or switch to MySQL below.</p>
            </div>
        @endif
        
        <p class="text-sm text-gray-600 mb-4 text-center">SQLite is set up by default. You can continue with SQLite or configure MySQL if you prefer.</p>

        @if (isset($errors) && $errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Database Connection Error:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('install.step1.store') }}" method="POST" id="databaseForm">
            @csrf
            <div class="mb-4">
                <label for="db_connection" class="block text-gray-700 text-sm font-bold mb-2">Database Type</label>
                <select name="db_connection" id="db_connection" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="sqlite" {{ ($db_connection ?? 'sqlite') === 'sqlite' ? 'selected' : '' }}>SQLite (Recommended for quick setup)</option>
                    <option value="mysql" {{ ($db_connection ?? '') === 'mysql' ? 'selected' : '' }}>MySQL</option>
                </select>
            </div>

            <div id="mysqlFields">
                <div class="mb-4">
                    <label for="db_host" class="block text-gray-700 text-sm font-bold mb-2">DB Host</label>
                    <input type="text" name="db_host" id="db_host" value="{{ old('db_host', $db_host ?? '127.0.0.1') }}" placeholder="127.0.0.1 or localhost" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-xs text-gray-500 mt-1">Default: 127.0.0.1 (localhost)</p>
                </div>
                <div class="mb-4">
                    <label for="db_port" class="block text-gray-700 text-sm font-bold mb-2">DB Port</label>
                    <input type="text" name="db_port" id="db_port" value="{{ old('db_port', $db_port ?? '3306') }}" placeholder="3306" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-xs text-gray-500 mt-1">Default: 3306</p>
                </div>
                <div class="mb-4">
                    <label for="db_database" class="block text-gray-700 text-sm font-bold mb-2">Database Name</label>
                    <input type="text" name="db_database" id="db_database" value="{{ old('db_database', $db_database ?? 'laravel') }}" placeholder="laravel" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-xs text-gray-500 mt-1">Database will be created automatically if it doesn't exist</p>
                </div>
                <div class="mb-4">
                    <label for="db_username" class="block text-gray-700 text-sm font-bold mb-2">DB Username</label>
                    <input type="text" name="db_username" id="db_username" value="{{ old('db_username', $db_username ?? 'root') }}" placeholder="root" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-xs text-gray-500 mt-1">Default: root</p>
                </div>
                <div class="mb-6">
                    <label for="db_password" class="block text-gray-700 text-sm font-bold mb-2">DB Password</label>
                    <input type="password" name="db_password" id="db_password" value="{{ old('db_password', $db_password ?? '') }}" placeholder="Leave empty if no password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:shadow-outline">
                    <p class="text-xs text-gray-500 mt-1">Leave empty if your MySQL user has no password</p>
                </div>
            </div>

            <div id="sqliteInfo" class="mb-6 hidden">
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded">
                    <p class="text-sm">SQLite will be created automatically at <code class="bg-blue-100 px-1 rounded">database/database.sqlite</code></p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                @if (isset($is_sqlite_configured) && $is_sqlite_configured)
                    <a href="{{ route('install.step4') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline flex-1 text-center">
                        Continue with SQLite
                    </a>
                @endif
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline {{ isset($is_sqlite_configured) && $is_sqlite_configured ? 'flex-1' : 'w-full' }}">
                    {{ isset($is_sqlite_configured) && $is_sqlite_configured ? 'Switch to MySQL' : 'Save & Continue' }}
                </button>
            </div>
            @php
                $status = $status ?? \App\Services\InstallationStatus::getStatus();
            @endphp
            @if ($status['current_step'] > 1)
                <div class="mt-3 text-center">
                    <a href="{{ route('install.reset') }}" class="text-xs text-red-600 hover:text-red-800 underline" onclick="return confirm('Are you sure you want to reset the installation? This will clear all progress and you will need to start from the beginning.');">
                        Reset Installation
                    </a>
                </div>
            @endif
        </form>

        <script>
            document.getElementById('db_connection').addEventListener('change', function() {
                const mysqlFields = document.getElementById('mysqlFields');
                const sqliteInfo = document.getElementById('sqliteInfo');
                const mysqlInputs = mysqlFields.querySelectorAll('input');

                if (this.value === 'sqlite') {
                    mysqlFields.classList.add('hidden');
                    sqliteInfo.classList.remove('hidden');
                    mysqlInputs.forEach(input => {
                        input.removeAttribute('required');
                    });
                } else {
                    mysqlFields.classList.remove('hidden');
                    sqliteInfo.classList.add('hidden');
                    mysqlInputs.forEach(input => {
                        if (input.id !== 'db_password') {
                            input.setAttribute('required', 'required');
                        }
                    });
                }
            });

            // Trigger on page load
            document.getElementById('db_connection').dispatchEvent(new Event('change'));
        </script>
    </div>
</body>
</html>
