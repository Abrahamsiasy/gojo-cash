<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InstallController extends Controller
{
    public function __construct(private \App\Services\CompanyService $companyService) {}

    public function step3()
    {
        // Step 3: Admin creation
        $status = \App\Services\InstallationStatus::getStatus();

        // Check if we can access this step
        if (! \App\Services\InstallationStatus::canAccessStep(3)) {
            return redirect()->route(\App\Services\InstallationStatus::getCurrentStepRoute());
        }

        // Ensure migrations have run - check the marker file, not just the table
        $migrateFile = storage_path('app/.migrations_run');
        if (! File::exists($migrateFile)) {
            return redirect()->route('install.step2')
                ->with('error', 'Please run migrations first. The migration step must be completed.');
        }

        // Verify migrations table exists and has all migrations
        try {
            if (! DB::getSchemaBuilder()->hasTable('migrations')) {
                return redirect()->route('install.step2')
                    ->with('error', 'Please run migrations first.');
            }

            // Verify all migrations have run
            $migrationFiles = glob(database_path('migrations/*.php'));
            $expectedCount = count($migrationFiles);
            $actualCount = DB::table('migrations')->count();

            if ($actualCount < $expectedCount) {
                return redirect()->route('install.step2')
                    ->with('error', "Only {$actualCount} of {$expectedCount} migrations have run. Please complete the migration step first.");
            }
        } catch (\Exception $e) {
            return redirect()->route('install.step2')
                ->with('error', 'Please run migrations first.');
        }

        return view('install.index');
    }

    public function database()
    {
        // Step 1: Database setup
        $status = \App\Services\InstallationStatus::getStatus();

        // Allow access to step 1 even if database is configured (so users can go back)
        // Only redirect if installation is complete
        if ($status['installation_complete']) {
            return redirect()->route('home');
        }

        // Don't auto-redirect - let users access step 1 even if they're on a later step
        // This allows them to go back and reconfigure if needed

        // Ensure .env exists before showing database setup
        \App\Support\EnvEditor::ensureExists();

        // Read default values from .env.bak
        $defaults = \App\Support\EnvEditor::readMultiple([
            'DB_CONNECTION',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
        ]);

        // Set defaults if not found - MySQL is preferred
        $dbConnection = $defaults['DB_CONNECTION'] ?? 'mysql';
        $dbHost = $defaults['DB_HOST'] ?? '127.0.0.1';
        $dbPort = $defaults['DB_PORT'] ?? '3306';
        $dbDatabase = $defaults['DB_DATABASE'] ?? 'laravel';
        $dbUsername = $defaults['DB_USERNAME'] ?? 'root';
        $dbPassword = $defaults['DB_PASSWORD'] ?? '';

        // Check current connection
        $currentConnection = env('DB_CONNECTION', 'mysql');
        $isSqliteConfigured = $currentConnection === 'sqlite' && $status['database_configured'];

        return view('install.database', [
            'db_connection' => $dbConnection,
            'db_host' => $dbHost,
            'db_port' => $dbPort,
            'db_database' => $dbDatabase,
            'db_username' => $dbUsername,
            'db_password' => $dbPassword,
            'is_sqlite_configured' => $isSqliteConfigured,
            'status' => $status,
        ]);
    }

    public function storeDatabase(Request $request)
    {
        $request->validate([
            'db_connection' => ['required', 'string', 'in:mysql,sqlite'],
            'db_host' => ['required_if:db_connection,mysql', 'string'],
            'db_port' => ['required_if:db_connection,mysql', 'string'],
            'db_database' => ['required_if:db_connection,mysql', 'string'],
            'db_username' => ['required_if:db_connection,mysql', 'string'],
            'db_password' => ['nullable', 'string'],
        ]);

        // Ensure .env exists
        \App\Support\EnvEditor::ensureExists();

        $dbConnection = $request->db_connection;

        if ($dbConnection === 'mysql') {
            // Validate MySQL credentials BEFORE saving to .env
            $testResult = \App\Services\InstallationService::testMysqlConnection(
                $request->db_host,
                $request->db_port,
                $request->db_database,
                $request->db_username,
                $request->db_password ?? ''
            );

            if (! $testResult['success']) {
                return back()
                    ->withErrors(['db_error' => implode(' ', $testResult['errors'])])
                    ->withInput();
            }

            // Credentials are valid, update .env with production settings
            \App\Support\EnvEditor::update([
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_database,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password ?? '',
            ]);
        } else {
            // Handle SQLite
            $dbPath = database_path('database.sqlite');
            $dbDir = dirname($dbPath);

            // Ensure database directory exists
            if (! File::exists($dbDir)) {
                File::makeDirectory($dbDir, 0755, true);
            }

            // Create SQLite database file if it doesn't exist
            if (! File::exists($dbPath)) {
                File::put($dbPath, '');
            }

            // Update .env for SQLite with production settings
            \App\Support\EnvEditor::update([
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => $dbPath,
            ]);
        }

        // Clear config cache to ensure new .env values are picked up
        \Illuminate\Support\Facades\Artisan::call('config:clear');

        // Try to connect and migrate
        try {
            if ($dbConnection === 'mysql') {
                // Ensure database exists before connecting
                try {
                    $pdo = new \PDO(
                        "mysql:host={$request->db_host};port={$request->db_port}",
                        $request->db_username,
                        $request->db_password
                    );
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    // Create database if it doesn't exist
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$request->db_database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                } catch (\PDOException $e) {
                    return back()
                        ->withErrors(['db_error' => "Failed to create database: {$e->getMessage()}"])
                        ->withInput();
                }

                // Force reconnection with new config
                config([
                    'database.connections.mysql.host' => $request->db_host,
                    'database.connections.mysql.port' => $request->db_port,
                    'database.connections.mysql.database' => $request->db_database,
                    'database.connections.mysql.username' => $request->db_username,
                    'database.connections.mysql.password' => $request->db_password,
                ]);

                DB::purge('mysql');
                DB::reconnect('mysql');
            } else {
                // SQLite - ensure file exists and is writable
                $dbPath = database_path('database.sqlite');
                if (! File::exists($dbPath)) {
                    File::put($dbPath, '');
                }
                DB::purge('sqlite');
                DB::reconnect('sqlite');
            }

            // Verify database connection is working
            try {
                DB::connection()->getPdo();
            } catch (\Exception $verifyException) {
                return back()
                    ->withErrors(['db_error' => 'Database connection verification failed: '.$verifyException->getMessage()])
                    ->withInput();
            }

        } catch (\Exception $e) {
            // Provide specific error messages
            $errorMessage = 'Could not connect to database: ';

            if ($e instanceof \PDOException) {
                $errorCode = $e->getCode();
                if ($errorCode == 1045) {
                    $errorMessage = 'Invalid username or password. Please check your MySQL credentials.';
                } elseif ($errorCode == 1049) {
                    $errorMessage = "Database '{$request->db_database}' does not exist and could not be created.";
                } elseif ($errorCode == 2002) {
                    $errorMessage = "Cannot connect to MySQL server. Please check if MySQL is running on {$request->db_host}:{$request->db_port}.";
                } else {
                    $errorMessage .= $e->getMessage();
                }
            } else {
                $errorMessage .= $e->getMessage();
            }

            return back()->withErrors(['db_error' => $errorMessage])->withInput();
        }

        // Clear config cache one more time to ensure everything is fresh
        \Illuminate\Support\Facades\Artisan::call('config:clear');

        // After successful database configuration, redirect to migration step
        return redirect()->route('install.step2')
            ->with('success', 'Database configured successfully! Now run migrations to set up the database.');
    }

    public function step2()
    {
        // Step 2: Run migrations
        $status = \App\Services\InstallationStatus::getStatus();

        // Check if we can access this step
        if (! \App\Services\InstallationStatus::canAccessStep(2)) {
            return redirect()->route(\App\Services\InstallationStatus::getCurrentStepRoute());
        }

        // Ensure database connection exists
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            return redirect()->route('install.step1')
                ->with('error', 'Database connection failed. Please configure the database first.');
        }

        // Check if migrations have already run
        $migrateFile = storage_path('app/.migrations_run');
        if (File::exists($migrateFile)) {
            try {
                if (DB::getSchemaBuilder()->hasTable('migrations') && DB::table('migrations')->count() > 0) {
                    return redirect()->route('install.step3')
                        ->with('info', 'Migrations have already been run.');
                }
            } catch (\Exception $e) {
                // Table doesn't exist or empty, continue
            }
        }

        return view('install.migrate');
    }

    public function storeMigrate(Request $request)
    {
        // Run migrate:fresh to drop all tables and run all migrations
        try {
            // Run migrate:fresh which will:
            // 1. Drop all tables
            // 2. Create migrations table
            // 3. Run all migrations from scratch
            $exitCode = \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
                '--force' => true,
            ]);

            // Check if command failed
            if ($exitCode !== 0) {
                throw new \Exception('Migration command returned non-zero exit code: '.$exitCode);
            }

            // Get the output to check for errors
            $output = \Illuminate\Support\Facades\Artisan::output();

            // Small delay to ensure migrations are fully committed
            usleep(500000); // 0.5 seconds

            // Reconnect to ensure fresh connection
            DB::purge();
            DB::reconnect();

            // Verify migrations table exists and has entries
            if (! DB::getSchemaBuilder()->hasTable('migrations')) {
                return back()
                    ->withErrors(['migrate_error' => 'Migrations table was not created. Please try again.']);
            }

            // Check that migrations actually ran by counting entries
            $migrationCount = DB::table('migrations')->count();
            if ($migrationCount === 0) {
                return back()
                    ->withErrors(['migrate_error' => 'No migrations were recorded. Please try again.']);
            }

            // Get list of all migration files to verify they all ran
            $migrationFiles = glob(database_path('migrations/*.php'));
            $expectedCount = count($migrationFiles);

            if ($migrationCount < $expectedCount) {
                return back()
                    ->withErrors(['migrate_error' => "Only {$migrationCount} of {$expectedCount} migrations ran. Some migrations may have failed. Please check the logs and try again."]);
            }

            // Verify key tables exist
            $requiredTables = ['users', 'companies', 'accounts', 'banks', 'transactions', 'transaction_categories', 'clients'];
            $missingTables = [];
            foreach ($requiredTables as $table) {
                if (! DB::getSchemaBuilder()->hasTable($table)) {
                    $missingTables[] = $table;
                }
            }

            if (! empty($missingTables)) {
                return back()
                    ->withErrors(['migrate_error' => 'Some required tables are missing: '.implode(', ', $missingTables).'. Please try again.']);
            }

            // Run PermissionSeeder to create all required permissions
            try {
                $seederExitCode = \Illuminate\Support\Facades\Artisan::call('db:seed', [
                    '--class' => 'PermissionSeeder',
                    '--force' => true,
                ]);

                if ($seederExitCode !== 0) {
                    throw new \Exception('PermissionSeeder returned non-zero exit code: '.$seederExitCode);
                }
            } catch (\Exception $seederException) {
                return back()
                    ->withErrors(['migrate_error' => 'Migrations completed but PermissionSeeder failed: '.$seederException->getMessage()]);
            }

            // Mark migrations as run
            $migrateFile = storage_path('app/.migrations_run');
            File::put($migrateFile, now()->toDateTimeString());

            // Clear config cache
            \Illuminate\Support\Facades\Artisan::call('config:clear');

            return redirect()->route('install.step3')
                ->with('success', "Database migrations completed successfully! All {$migrationCount} migration(s) ran and permissions have been seeded.");
        } catch (\Exception $e) {
            return back()
                ->withErrors(['migrate_error' => 'Migration failed: '.$e->getMessage()]);
        }
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create super-admin role if it doesn't exist
        $role = Role::firstOrCreate(['name' => 'super-admin']);

        // Assign super-admin role to the user
        $user->assignRole('super-admin');

        // Give all permissions to super-admin role
        $role->givePermissionTo(Permission::all());

        Auth::login($user);

        return redirect()->route('install.step4');
    }

    public function step4()
    {
        // Step 4: Company creation
        $status = \App\Services\InstallationStatus::getStatus();

        // Check if we can access this step
        if (! \App\Services\InstallationStatus::canAccessStep(4)) {
            return redirect()->route(\App\Services\InstallationStatus::getCurrentStepRoute());
        }

        return view('install.company');
    }

    public function storeCompany(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:companies,name'],
        ]);

        $this->companyService->createCompany([
            'name' => $request->name,
            'status' => true,
        ]);

        // Mark installation as complete
        $this->markInstallationComplete();

        // Clear config to ensure status is refreshed
        \Illuminate\Support\Facades\Artisan::call('config:clear');

        // Redirect to dashboard after successful installation
        return redirect()->route('dashboard')
            ->with('success', 'Installation completed successfully! Welcome to your dashboard.');
    }

    /**
     * Reset installation progress and go back to step 1.
     * Only works during installation, not after completion.
     * Also resets .env file by copying from .env.bak to start fresh.
     */
    public function reset()
    {
        $status = \App\Services\InstallationStatus::getStatus();

        // Prevent reset if installation is already complete
        if ($status['installation_complete']) {
            return redirect()->route('home')
                ->with('error', 'Installation is complete and locked. Cannot reset.');
        }

        // Delete all installation progress files
        $files = [
            storage_path('app/.installed'),
            storage_path('app/.migrations_run'),
        ];

        foreach ($files as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }

        // Reset .env file by copying from .env.bak (fresh start)
        $envPath = base_path('.env');
        $envBakPath = base_path('.env.bak');

        if (File::exists($envBakPath)) {
            // Copy .env.bak to .env to reset all configuration
            $content = File::get($envBakPath);

            // Ensure required values are set for installation
            if (preg_match('/^SESSION_DRIVER=database/m', $content)) {
                $content = preg_replace('/^SESSION_DRIVER=database/m', 'SESSION_DRIVER=file', $content);
            } elseif (! preg_match('/^SESSION_DRIVER=/m', $content)) {
                if (preg_match('/(SESSION_DOMAIN=.*)/m', $content)) {
                    $content = preg_replace('/(SESSION_DOMAIN=.*)/m', "$1\nSESSION_DRIVER=file", $content);
                } else {
                    $content .= "\nSESSION_DRIVER=file\n";
                }
            }

            // Ensure APP_ENV is production
            if (preg_match('/^APP_ENV=(local|development|dev|prodcution)/m', $content)) {
                $content = preg_replace('/^APP_ENV=(local|development|dev|prodcution)/m', 'APP_ENV=production', $content);
            }

            // Ensure APP_DEBUG is false
            if (preg_match('/^APP_DEBUG=(true|1)/m', $content)) {
                $content = preg_replace('/^APP_DEBUG=(true|1)/m', 'APP_DEBUG=false', $content);
            }

            // Write fresh .env from .env.bak
            File::put($envPath, $content);
        }

        // Clear config cache
        \Illuminate\Support\Facades\Artisan::call('config:clear');

        return redirect()->route('install.step1')
            ->with('info', 'Installation has been reset. .env file has been restored from .env.bak. You can start from the beginning.');
    }

    /**
     * Mark installation as complete by creating a lock file.
     */
    protected function markInstallationComplete(): void
    {
        $lockFile = storage_path('app/.installed');
        File::put($lockFile, now()->toDateTimeString());
    }

    public function testCreateCompany() {}
}
