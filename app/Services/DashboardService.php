<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get dashboard data for super-admin (all companies overview)
     */
    public function getSuperAdminDashboardData(): array
    {
        return [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('status', true)->count(),
            'inactive_companies' => Company::where('status', false)->count(),
            'total_users' => User::count(),
            'total_accounts' => Account::count(),
            'total_transactions' => Transaction::count(),
            'companies_by_status' => $this->getCompaniesByStatus(),
            'recent_companies' => $this->getRecentCompanies(5),
            'companies_with_most_users' => $this->getCompaniesWithMostUsers(5),
            'companies_with_most_transactions' => $this->getCompaniesWithMostTransactions(5),
            'total_revenue_all_companies' => $this->getTotalRevenueAllCompanies(),
            'monthly_company_growth' => $this->getMonthlyCompanyGrowth(),
            'top_performing_companies' => $this->getTopPerformingCompanies(5),
        ];
    }

    /**
     * Get dashboard data for regular company users
     */
    public function getCompanyDashboardData(int $companyId): array
    {
        $company = Company::findOrFail($companyId);
        $companyService = app(CompanyService::class);

        return [
            'company' => $company,
            'metrics' => $companyService->getCompanyMetrics($company, []),
        ];
    }

    private function getCompaniesByStatus(): array
    {
        return [
            'active' => Company::where('status', true)->count(),
            'inactive' => Company::where('status', false)->count(),
            'trial' => Company::whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>', now())
                ->count(),
        ];
    }

    private function getRecentCompanies(int $limit): Collection
    {
        return Company::latest()
            ->limit($limit)
            ->withCount(['users', 'accounts'])
            ->get();
    }

    private function getCompaniesWithMostUsers(int $limit): Collection
    {
        return Company::withCount('users')
            ->orderByDesc('users_count')
            ->limit($limit)
            ->get();
    }

    private function getCompaniesWithMostTransactions(int $limit): Collection
    {
        return Company::withCount('transactions')
            ->orderByDesc('transactions_count')
            ->limit($limit)
            ->get();
    }

    private function getTotalRevenueAllCompanies(): float
    {
        return Transaction::where('type', 'income')
            ->sum('amount');
    }

    private function getMonthlyCompanyGrowth(): array
    {
        // Get company creation count by month for last 12 months
        return Company::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
    }

    private function getTopPerformingCompanies(int $limit): Collection
    {
        // Companies with highest net income
        $companies = Company::all();

        return $companies->map(function ($company) {
            $income = Transaction::where('company_id', $company->id)
                ->where('type', 'income')
                ->sum('amount');
            $expense = Transaction::where('company_id', $company->id)
                ->where('type', 'expense')
                ->sum('amount');
            $company->net_income = $income - $expense;

            return $company;
        })
            ->sortByDesc('net_income')
            ->take($limit)
            ->values();
    }
}
