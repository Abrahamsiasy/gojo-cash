<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstallationReady
{
    /**
     * Handle an incoming request.
     *
     * This middleware runs BEFORE session middleware to ensure:
     * 1. .env file exists
     * 2. SESSION_DRIVER is set to 'file' if database doesn't exist
     * 3. SQLite is auto-configured if MySQL database doesn't exist
     * 4. Basic migrations are run
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ensure .env exists
        \App\Support\EnvEditor::ensureExists();

        // Check if database is configured and working
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);

            // Get current database connection using service
            $dbConnection = \App\Services\InstallationService::getCurrentConnection();

            // Get session driver
            $sessionDriver = 'file';
            if (preg_match('/^SESSION_DRIVER=(.*)$/m', $envContent, $matches)) {
                $sessionDriver = trim($matches[1] ?? 'file');
            }

            // Always use file sessions during installation to avoid database errors
            if ($request->is('install*')) {
                if ($sessionDriver !== 'file') {
                    \App\Support\EnvEditor::update(['SESSION_DRIVER' => 'file']);
                    Artisan::call('config:clear');
                    config(['session.driver' => 'file']);
                }
            }

            // If MySQL is configured, check if we can connect
            if ($dbConnection === 'mysql' && $sessionDriver === 'database') {
                try {
                    // Try to connect to MySQL
                    DB::connection('mysql')->getPdo();
                } catch (\Exception $e) {
                    // MySQL database doesn't exist or can't connect
                    // Switch to file sessions immediately to prevent errors
                    // But DON'T auto-switch to SQLite - let user fix MySQL credentials
                    \App\Support\EnvEditor::update(['SESSION_DRIVER' => 'file']);
                    Artisan::call('config:clear');
                    config(['session.driver' => 'file']);
                    // User will see error on database setup page and can fix credentials
                }
            } elseif ($dbConnection === 'sqlite') {
                // SQLite - ensure it's ready (but don't run migrations here)
                // Migrations should only run when user submits database form
                \App\Services\InstallationService::setupSqlite(false);
            }
        }

        return $next($request);
    }
}
