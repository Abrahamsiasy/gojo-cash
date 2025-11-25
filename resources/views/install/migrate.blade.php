<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install - Migrate Database</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="mb-4">
            <div class="flex items-center justify-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center font-bold">✓</div>
                <div class="h-1 w-12 bg-blue-500"></div>
                <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold">2</div>
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
        <h1 class="text-2xl font-bold mb-6 text-center">Installation - Step 2</h1>
        <h2 class="text-xl mb-4 text-center">Migrate Database</h2>
        <p class="text-sm text-gray-600 mb-4 text-center">Run database migrations to set up all required tables and relationships.</p>
        
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <p class="text-sm font-semibold text-blue-900 mb-2">What happens next:</p>
            <ol class="text-sm text-blue-800 list-decimal list-inside space-y-1">
                <li>Run migrations to set up Laravel's migration system</li>
                <li>Create admin account (Step 3)</li>
                <li>Create company (Step 4)</li>
                <li>Redirect to dashboard</li>
            </ol>
        </div>

        <form action="{{ route('install.step2.store') }}" method="POST">
            @csrf
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-4">This will run <code class="bg-gray-100 px-2 py-1 rounded">php artisan migrate:fresh --force</code> which will:</p>
                <ul class="text-sm text-gray-600 list-disc list-inside space-y-1 mt-2">
                    <li>Drop all existing tables</li>
                    <li>Recreate all tables from Laravel migration files</li>
                    <li>Run all pending migrations</li>
                </ul>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('install.step1') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline text-center">
                    ← Back to Step 1
                </a>
                <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Run Migrations
                </button>
            </div>
            <div class="mt-3 text-center">
                <a href="{{ route('install.reset') }}" class="text-xs text-red-600 hover:text-red-800 underline" onclick="return confirm('Are you sure you want to reset the installation? This will clear all progress and you will need to start from the beginning.');">
                    Reset Installation
                </a>
            </div>
        </form>
    </div>
</body>
</html>

