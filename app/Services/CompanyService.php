<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CompanyService
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
        return Company::query()
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where(static function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
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

    public function prepareShowData(Company $company, ?string $search, int $perPage = 10): array
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
        ];
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

    public function getCompanyMetrics(Company $company): array
    {
        $accountStats = Account::query()
            ->selectRaw('COUNT(*) as total_accounts, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_accounts, SUM(balance) as total_balance')
            ->where('company_id', $company->id)
            ->first();

        $transactionStats = Transaction::query()
            ->selectRaw('COUNT(*) as total_transactions, SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income, SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expense')
            ->where('company_id', $company->id)
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
        return TransactionCategory::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
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
}
