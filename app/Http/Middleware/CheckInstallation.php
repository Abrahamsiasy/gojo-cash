<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get installation status
        $status = \App\Services\InstallationStatus::getStatus();

        // If installation is complete, block all install routes
        if ($status['installation_complete']) {
            if ($request->is('install*')) {
                return redirect()->route('home');
            }

            return $next($request);
        }

        // Determine which step the user should be on
        $currentStepRoute = \App\Services\InstallationStatus::getCurrentStepRoute();

        // Map routes to step numbers
        $routeSteps = [
            'install.step1' => 1,
            'install.step1.store' => 1,
            'install.step2' => 2,
            'install.step2.store' => 2,
            'install.step3' => 3,
            'install.step3.store' => 3,
            'install.step4' => 4,
            'install.step4.store' => 4,
        ];

        // Get the step number for the current route
        $requestedStep = null;
        foreach ($routeSteps as $routeName => $step) {
            if ($request->routeIs($routeName)) {
                $requestedStep = $step;
                break;
            }
        }

        // If accessing install routes, check if step is allowed
        if ($request->is('install*') && $requestedStep !== null) {
            // Always allow access to step 1 (database) - users can always go back
            if ($requestedStep === 1) {
                return $next($request);
            }

            // Check if user can access this step
            if (! \App\Services\InstallationStatus::canAccessStep($requestedStep)) {
                // Redirect to current step
                return redirect()->route($currentStepRoute);
            }
        }

        // If trying to access non-install routes during installation, redirect to current step
        if (! $request->is('install*') && ! $status['installation_complete']) {
            return redirect()->route($currentStepRoute);
        }

        return $next($request);
    }
}
