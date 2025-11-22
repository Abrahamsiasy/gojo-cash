<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class InstallationStatus
{
    /**
     * Check installation status and return current step.
     */
    public static function getStatus(): array
    {
        $status = [
            'database_configured' => false,
            'migrations_run' => false,
            'admin_created' => false,
            'company_created' => false,
            'installation_complete' => false,
            'current_step' => 1, // 1=Database, 2=Migrate, 3=Admin, 4=Company, 5=Complete
        ];

        // Check if installation is locked (completed)
        $lockFile = storage_path('app/.installed');
        if (File::exists($lockFile)) {
            $status['installation_complete'] = true;
            $status['current_step'] = 6;

            return $status;
        }

        // Check database connection
        try {
            DB::connection()->getPdo();
            $status['database_configured'] = true;
        } catch (\Exception $e) {
            // Database not configured - Step 1
            return $status;
        }

        // Check if migrations have run
        $migrateFile = storage_path('app/.migrations_run');
        try {
            // Check if the migrations run marker file exists (this is set after migrate command runs)
            if (! File::exists($migrateFile)) {
                // Migrations haven't been run via the web interface - Step 2
                $status['current_step'] = 2;

                return $status;
            }

            // If marker file exists, verify that migrations table exists and has entries
            if (! Schema::hasTable('migrations')) {
                // Marker file exists but migrations table doesn't - Step 2
                $status['current_step'] = 2;

                return $status;
            }

            // Verify that all migration files have been run
            $migrationFiles = glob(database_path('migrations/*.php'));
            $expectedCount = count($migrationFiles);
            $actualCount = DB::table('migrations')->count();

            // If not all migrations have run, go to step 2
            if ($actualCount < $expectedCount) {
                $status['current_step'] = 2;

                return $status;
            }

            // All migrations have run
            $status['migrations_run'] = true;
        } catch (\Exception $e) {
            // Can't check tables - Step 2
            $status['current_step'] = 2;

            return $status;
        }

        // Check if admin user exists
        try {
            if (User::exists()) {
                $status['admin_created'] = true;
            } else {
                // Migrations run but no admin - Step 3
                $status['current_step'] = 3;

                return $status;
            }
        } catch (\Exception $e) {
            // Can't check users - Step 3
            $status['current_step'] = 3;

            return $status;
        }

        // Check if company exists
        try {
            if (Company::exists()) {
                $status['company_created'] = true;
                $status['installation_complete'] = true;
                $status['current_step'] = 5;
            } else {
                // Admin exists but no company - Step 4
                $status['current_step'] = 4;

                return $status;
            }
        } catch (\Exception $e) {
            // Can't check companies - Step 4
            $status['current_step'] = 4;

            return $status;
        }

        return $status;
    }

    /**
     * Get the route for the current step.
     */
    public static function getCurrentStepRoute(): string
    {
        $status = self::getStatus();

        if ($status['installation_complete']) {
            return 'home';
        }

        return match ($status['current_step']) {
            1 => 'install.step1',
            2 => 'install.step2',
            3 => 'install.step3',
            4 => 'install.step4',
            default => 'install.step1',
        };
    }

    /**
     * Check if a specific step is allowed.
     */
    public static function canAccessStep(int $step): bool
    {
        $status = self::getStatus();

        if ($status['installation_complete']) {
            return false; // Installation complete, no access to install routes
        }

        $currentStep = $status['current_step'];

        // Always allow access to step 1 (database configuration) - users can always go back
        if ($step === 1) {
            return true;
        }

        // Can access current step, any previous step (to allow going back), or one step ahead
        // This allows users to go back if they get stuck
        return $step <= $currentStep + 1;
    }
}
