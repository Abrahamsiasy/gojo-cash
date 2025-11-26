<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user) {
            abort(401, __('Unauthorized'));
        }

        if ($user->hasRole('super-admin')) {
            return view('admin.dashboard.super-admin',
                $this->dashboardService->getSuperAdminDashboardData()
            );
        }

        // Regular users must have a company_id
        if (! $user->company_id) {
            abort(403, __('You must be assigned to a company to access the dashboard.'));
        }

        return view('dashboard',
            $this->dashboardService->getCompanyDashboardData($user->company_id)
        );
    }
}
