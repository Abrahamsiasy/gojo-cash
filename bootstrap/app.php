<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Run this BEFORE session middleware to ensure database is ready
        // $middleware->web(prepend: [
        //     \App\Http\Middleware\EnsureInstallationReady::class,
        // ]);
        // $middleware->web(append: [
        //     \App\Http\Middleware\CheckInstallation::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // Auto-setup SQLite if database errors occur during installation
            if ($e instanceof \Illuminate\Database\QueryException || $e instanceof \PDOException) {
                // Check if we're trying to use MySQL but database doesn't exist
                $errorCode = $e->getCode();
                $isDatabaseError = in_array($errorCode, ['1049', 1049, '2002', 2002], true);

                if ($isDatabaseError && ! $request->is('install/database*')) {
                    // Try to auto-setup SQLite
                    try {
                        $currentConnection = \App\Services\InstallationService::getCurrentConnection();
                        if ($currentConnection === 'mysql') {
                            \App\Services\InstallationService::setupSqlite(false);

                            // Redirect to install index to continue setup
                            return redirect()->route('install.step4');
                        }
                    } catch (\Exception $setupException) {
                        // If auto-setup fails, redirect to database setup page
                        return redirect()->route('install.step1');
                    }

                    // If already on install routes, redirect to database setup
                    return redirect()->route('install.step1');
                }
            }

            return null;
        });
    })->create();
