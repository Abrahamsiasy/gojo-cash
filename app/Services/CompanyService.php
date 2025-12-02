<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Client;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyService extends BaseService
{
    public function getIndexData(?string $search, int $perPage = 15): array
    {
        $companies = $this->paginateCompanies($search, $perPage);

        return [
            'headers' => $this->getIndexHeaders(),
            'rows' => $this->buildCompanyRows($companies),
            'companies' => $companies,
            'search' => $search ?? '',
        ];
    }

    public function paginateCompanies(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        return Company::query()
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where(static function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
            })
            ->when($user && ! $user->hasRole('super-admin') && $user->company_id, function ($query) use ($user) {
                $query->where('id', $user->company_id);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getIndexHeaders(): array
    {
        return [
            '#',
            __('Name'),
            __('Slug'),
            __('Status'),
            __('Trial Ends'),
            __('Created'),
        ];
    }

    public function buildCompanyRows(LengthAwarePaginator $companies): Collection
    {
        return collect($companies->items())->map(function (Company $company, int $index) use ($companies) {
            $position = ($companies->firstItem() ?? 1) + $index;

            return [
                'id' => $company->id,
                'name' => $company->name,
                'model' => $company, // Include model instance for policy checks
                'cells' => [
                    $position,
                    $company->name,
                    $company->slug,
                    $company->status ? __('Active') : __('Inactive'),
                    optional($company->trial_ends_at)?->translatedFormat('M j, Y') ?? __('—'),
                    $company->created_at?->translatedFormat('M j, Y'),
                ],
                'actions' => [
                    'view' => [
                        'url' => route('companies.show', $company),
                    ],
                    'edit' => [
                        'url' => route('companies.edit', $company),
                    ],
                    'delete' => [
                        'url' => route('companies.destroy', $company),
                        'confirm' => __('Are you sure you want to delete :company?', ['company' => $company->name]),
                    ],
                ],
            ];
        });
    }

    public function createCompany(array $data): Company
    {
        $data['slug'] = Str::slug($data['name']);

        return Company::create($data);
    }

    public function updateCompany(Company $company, array $data): Company
    {
        $data['slug'] = Str::slug($data['name']);

        $company->update($data);

        return $company;
    }

    public function deleteCompany(Company $company): void
    {
        $company->delete();
    }

    public function prepareShowData(Company $company, ?string $search, int $perPage = 10, array $filters = []): array
    {
        $company->loadCount('accounts');

        $accounts = $this->paginateCompanyAccounts($company, $search, $perPage);
        $companyAccountsList = $this->getCompanyAccountsList($company);

        return [
            'company' => $company,
            'headers' => $this->getCompanyAccountHeaders(),
            'rows' => $this->buildCompanyAccountRows($accounts),
            'search' => $search ?? '',
            'accounts' => $accounts,
            'metrics' => $this->getCompanyMetrics($company),
            'transactionCategories' => $this->getTransactionCategories($company),
            'companyAccounts' => $companyAccountsList,
            'transferAccounts' => $companyAccountsList,
            'statuses' => $this->getStatusOptions(),
            'accountTypeOptions' => $this->getAccountTypeOptions(),
            'banks' => $this->getBanks(),
            'clients' => $this->getClients(),
            'incomeExpenseChartData' => $this->getIncomeExpenseChartData($company, $filters),
            'transactionsByCategoryData' => $this->getTransactionsByCategoryData($company, $filters),
            'incomeByCategoryData' => $this->getIncomeByCategoryData($company, $filters),
            'transactionsByAccountData' => $this->getTransactionsByAccountData($company, $filters),
            'transactionsByTypeData' => $this->getTransactionsByTypeData($company, $filters),
            'financialInsights' => $this->getFinancialInsights($company, $filters),
        ];
    }

    private function applyTransactionFilters($query, array $filters): void
    {
        if (! empty($filters['account_id'])) {
            $query->where('transactions.account_id', $filters['account_id']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('transactions.category_id', $filters['category_id']);
        }
        if (! empty($filters['client_id'])) {
            $query->where('transactions.client_id', $filters['client_id']);
        }
        if (! empty($filters['date_from'])) {
            $query->where('transactions.date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('transactions.date', '<=', $filters['date_to']);
        }
    }

    public function paginateCompanyAccounts(Company $company, ?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Account::query()
            ->where('company_id', $company->id)
            ->with(['bank', 'company'])
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where(static function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('account_number', 'like', '%'.$search.'%')
                        ->orWhereHas('bank', static function ($bankQuery) use ($search) {
                            $bankQuery->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getCompanyAccountHeaders(): array
    {
        return [
            '#',
            __('Account Name'),
            __('Number'),
            __('Type'),
            __('Bank'),
            __('Balance'),
            __('Active'),
            __('Created'),
        ];
    }

    public function buildCompanyAccountRows(LengthAwarePaginator $accounts): Collection
    {
        return collect($accounts->items())->map(function (Account $account, int $index) use ($accounts) {
            $position = ($accounts->firstItem() ?? 1) + $index;

            return [
                'id' => $account->id,
                'name' => $account->name,
                'model' => $account, // Include model instance for policy checks
                'cells' => [
                    $position,
                    $account->name,
                    $account->account_number ?? __('—'),
                    $account->account_type?->value ?? __('—'),
                    $account->bank->name ?? __('—'),
                    number_format((float) $account->balance, 2),
                    $account->is_active ? __('Yes') : __('No'),
                    $account->created_at?->translatedFormat('M j, Y'),
                ],
                'actions' => [
                    'view' => [
                        'url' => route('accounts.show', $account),
                    ],
                    'edit' => [
                        'url' => route('accounts.edit', $account),
                    ],
                ],
            ];
        });
    }

    public function getCompanyMetrics(Company $company, array $filters = []): array
    {
        $accountStats = Account::query()
            ->selectRaw('COUNT(*) as total_accounts, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_accounts, SUM(balance) as total_balance')
            ->where('company_id', $company->id)
            ->first();

        $transactionQuery = Transaction::query()
            ->where('company_id', $company->id);

        $this->applyTransactionFilters($transactionQuery, $filters);

        $transactionStats = $transactionQuery
            ->selectRaw('COUNT(*) as total_transactions, SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income, SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expense')
            ->first();

        return [
            'total_accounts' => (int) ($accountStats->total_accounts ?? 0),
            'active_accounts' => (int) ($accountStats->active_accounts ?? 0),
            'inactive_accounts' => (int) (($accountStats->total_accounts ?? 0) - ($accountStats->active_accounts ?? 0)),
            'total_balance' => (float) ($accountStats->total_balance ?? 0),
            'total_transactions' => (int) ($transactionStats->total_transactions ?? 0),
            'total_income' => (float) ($transactionStats->total_income ?? 0),
            'total_expense' => (float) ($transactionStats->total_expense ?? 0),
        ];
    }

    public function getTransactionCategories(Company $company): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $query = TransactionCategory::query()
            ->where('company_id', $company->id);

        // Filter by user permissions if not super-admin
        if (! $user->hasRole('super-admin')) {
            $categoryTypes = [];
            if ($user->can('create income')) {
                $categoryTypes[] = 'income';
            }
            if ($user->can('create expense')) {
                $categoryTypes[] = 'expense';
            }

            // If user has no permissions, return empty array
            if (empty($categoryTypes)) {
                return [];
            }

            $query->whereIn('type', $categoryTypes);
        }

        return $query->orderBy('name')
            ->get()
            ->mapWithKeys(static function (TransactionCategory $category): array {
                return [
                    $category->id => $category->name.' ('.$category->type.')',
                ];
            })
            ->toArray();
    }

    public function getCompanyAccountsList(Company $company): array
    {
        return Account::query()
            ->where('company_id', $company->id)
            ->with('company')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static function (Account $account): array {
                $companyName = $account->company->name ?? __('—');

                return [
                    $account->id => $account->name.' ('.$companyName.')',
                ];
            })
            ->toArray();
    }

    public function getStatusOptions(): array
    {
        return [
            'pending' => __('Pending'),
            'approved' => __('Approved'),
            'rejected' => __('Rejected'),
        ];
    }

    public function getAccountTypeOptions(): array
    {
        return collect(AccountType::cases())
            ->mapWithKeys(static function (AccountType $type): array {
                return [
                    $type->value => Str::headline($type->name),
                ];
            })
            ->toArray();
    }

    public function getBanks(): array
    {
        return Bank::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getClients(): array
    {
        return Client::query()
            ->forCompany() // Filter by company
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getIncomeExpenseChartData(Company $company, array $filters = []): array
    {
        // Default to last 12 months if not provided in filters
        $dateFrom = isset($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from']) : now()->subMonths(11)->startOfMonth();
        $dateTo = isset($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to']) : now()->endOfMonth();

        $diffInDays = $dateFrom->diffInDays($dateTo);
        $groupBy = $diffInDays <= 90 ? 'day' : 'month';
        $dateFormat = $groupBy === 'day' ? '%Y-%m-%d' : '%Y-%m';
        $phpDateFormat = $groupBy === 'day' ? 'Y-m-d' : 'Y-m';
        $labelFormat = $groupBy === 'day' ? 'M j' : 'M Y';

        $query = Transaction::where('company_id', $company->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->whereIn('type', ['income', 'expense']);

        $this->applyTransactionFilters($query, \Illuminate\Support\Arr::except($filters, ['date_from', 'date_to']));

        $data = $query->selectRaw("DATE_FORMAT(date, '$dateFormat') as period, type, SUM(amount) as total")
            ->groupBy('period', 'type')
            ->orderBy('period')
            ->get();

        $labels = [];
        $incomeData = [];
        $expenseData = [];

        $current = $dateFrom->copy();
        while ($current <= $dateTo) {
            $key = $current->format($phpDateFormat);
            $label = $current->format($labelFormat);

            $labels[] = $label;

            $income = $data->where('period', $key)->where('type', 'income')->first()?->total ?? 0;
            $expense = $data->where('period', $key)->where('type', 'expense')->first()?->total ?? 0;

            $incomeData[] = (float) $income;
            $expenseData[] = (float) $expense;

            if ($groupBy === 'day') {
                $current->addDay();
            } else {
                $current->addMonth();
            }
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expense' => $expenseData,
        ];
    }

    public function getTransactionsByCategoryData(Company $company, array $filters = []): array
    {
        $query = Transaction::where('transactions.company_id', $company->id)
            ->where('transactions.type', 'expense')
            ->whereNotNull('transactions.category_id');

        $this->applyTransactionFilters($query, $filters);

        $data = $query->join('transaction_categories', 'transactions.category_id', '=', 'transaction_categories.id')
            ->selectRaw('transaction_categories.name, SUM(transactions.amount) as total')
            ->groupBy('transaction_categories.name')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'data' => $data->pluck('total')->map(fn ($val) => (float) $val)->toArray(),
        ];
    }

    public function getIncomeByCategoryData(Company $company, array $filters = []): array
    {
        $query = Transaction::where('transactions.company_id', $company->id)
            ->where('transactions.type', 'income')
            ->whereNotNull('transactions.category_id');

        $this->applyTransactionFilters($query, $filters);

        $data = $query->join('transaction_categories', 'transactions.category_id', '=', 'transaction_categories.id')
            ->selectRaw('transaction_categories.name, SUM(transactions.amount) as total')
            ->groupBy('transaction_categories.name')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'data' => $data->pluck('total')->map(fn ($val) => (float) $val)->toArray(),
        ];
    }

    public function getTransactionsByAccountData(Company $company, array $filters = []): array
    {
        $query = Transaction::where('transactions.company_id', $company->id);

        $this->applyTransactionFilters($query, $filters);

        $data = $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->selectRaw('accounts.name, COUNT(transactions.id) as count')
            ->groupBy('accounts.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'data' => $data->pluck('count')->map(fn ($val) => (int) $val)->toArray(),
        ];
    }

    public function getTransactionsByTypeData(Company $company, array $filters = []): array
    {
        $query = Transaction::where('company_id', $company->id);

        $this->applyTransactionFilters($query, $filters);

        $data = $query->selectRaw('type, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('type')
            ->get();

        $labels = $data->pluck('type')->map(fn ($type) => ucfirst($type))->toArray();
        $counts = $data->pluck('count')->map(fn ($val) => (int) $val)->toArray();
        $amounts = $data->pluck('total_amount')->map(fn ($val) => (float) $val)->toArray();

        return [
            'labels' => $labels,
            'counts' => $counts,
            'amounts' => $amounts,
        ];
    }

    public function getFinancialInsights(Company $company, array $filters = []): array
    {
        // 1. Net Cash Flow
        $query = Transaction::where('company_id', $company->id);
        $this->applyTransactionFilters($query, $filters);
        $totals = $query->selectRaw('
            SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expense
        ')->first();

        $netCashFlow = ($totals->total_income ?? 0) - ($totals->total_expense ?? 0);

        // 2. Top Expense Category
        $expenseQuery = Transaction::where('transactions.company_id', $company->id)
            ->where('transactions.type', 'expense')
            ->whereNotNull('transactions.category_id');
        $this->applyTransactionFilters($expenseQuery, $filters);
        $topExpense = $expenseQuery->join('transaction_categories', 'transactions.category_id', '=', 'transaction_categories.id')
            ->selectRaw('transaction_categories.name, SUM(transactions.amount) as total')
            ->groupBy('transaction_categories.name')
            ->orderByDesc('total')
            ->first();

        // 3. Top Income Category
        $incomeQuery = Transaction::where('transactions.company_id', $company->id)
            ->where('transactions.type', 'income')
            ->whereNotNull('transactions.category_id');
        $this->applyTransactionFilters($incomeQuery, $filters);
        $topIncome = $incomeQuery->join('transaction_categories', 'transactions.category_id', '=', 'transaction_categories.id')
            ->selectRaw('transaction_categories.name, SUM(transactions.amount) as total')
            ->groupBy('transaction_categories.name')
            ->orderByDesc('total')
            ->first();

        // 4. Most Active Account
        $accountQuery = Transaction::where('transactions.company_id', $company->id);
        $this->applyTransactionFilters($accountQuery, $filters);
        $activeAccount = $accountQuery->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->selectRaw('accounts.name, COUNT(transactions.id) as count')
            ->groupBy('accounts.name')
            ->orderByDesc('count')
            ->first();

        return [
            'net_cash_flow' => $netCashFlow,
            'top_expense_category' => $topExpense ? $topExpense->name : null,
            'top_expense_amount' => $topExpense ? (float) $topExpense->total : 0,
            'top_income_category' => $topIncome ? $topIncome->name : null,
            'top_income_amount' => $topIncome ? (float) $topIncome->total : 0,
            'most_active_account' => $activeAccount ? $activeAccount->name : null,
            'most_active_account_count' => $activeAccount ? (int) $activeAccount->count : 0,
        ];
    }
}
