<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InstallationService
{
    /**
     * Setup SQLite database and configure environment.
     */
    public static function setupSqlite(bool $runMigrations = true): void
    {
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

        // Update .env to use SQLite and file sessions
        \App\Support\EnvEditor::update([
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $dbPath,
            'SESSION_DRIVER' => 'file',
        ]);

        // Clear config and reload
        Artisan::call('config:clear');
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $dbPath,
            'session.driver' => 'file',
        ]);

        // Always run migrations after .env preparation
        if ($runMigrations) {
            try {
                DB::purge('sqlite');
                DB::reconnect('sqlite');

                // Check if migrations table exists, if not run migrations
                if (! DB::getSchemaBuilder()->hasTable('migrations')) {
                    Artisan::call('migrate', ['--force' => true]);
                }
            } catch (\Exception $e) {
                // Migration will be handled later
            }
        }
    }

    /**
     * Test MySQL database connection with provided credentials.
     */
    public static function testMysqlConnection(string $host, string $port, string $database, string $username, string $password): array
    {
        $errors = [];

        // Test connection to MySQL server (without database)
        try {
            $pdo = new \PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
            $errorCode = $e->getCode();

            if ($errorCode == 1045) {
                $errors[] = 'Invalid username or password. Please check your MySQL credentials.';
            } elseif ($errorCode == 2002) {
                $errors[] = "Cannot connect to MySQL server at {$host}:{$port}. Please check if MySQL is running and the host/port are correct.";
            } elseif ($errorCode == 2003) {
                $errors[] = "Connection refused. Please check if MySQL server is running on {$host}:{$port}.";
            } else {
                $errors[] = "Connection failed: {$e->getMessage()}";
            }

            return ['success' => false, 'errors' => $errors];
        }

        // Test if database exists, create it if it doesn't
        try {
            $pdo->exec("USE `{$database}`");
        } catch (\PDOException $e) {
            // Database doesn't exist, try to create it
            try {
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                // Verify database was created by trying to use it
                $pdo->exec("USE `{$database}`");
            } catch (\PDOException $createException) {
                $errors[] = "Database '{$database}' does not exist and cannot be created. Error: {$createException->getMessage()}";

                return ['success' => false, 'errors' => $errors];
            }
        }

        // Test connection with database
        try {
            $testPdo = new \PDO(
                "mysql:host={$host};port={$port};dbname={$database}",
                $username,
                $password,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
            $errors[] = "Cannot connect to database '{$database}'. Error: {$e->getMessage()}";

            return ['success' => false, 'errors' => $errors];
        }

        return ['success' => true, 'errors' => []];
    }

    /**
     * Get current database connection from .env file.
     */
    public static function getCurrentConnection(): string
    {
        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            return 'mysql';
        }

        $envContent = File::get($envPath);
        if (preg_match('/^DB_CONNECTION=(.*)$/m', $envContent, $matches)) {
            return trim($matches[1] ?? 'mysql');
        }

        return 'mysql';
    }
}
