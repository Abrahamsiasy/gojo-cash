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
use Illuminate\Support\Str;

class AccountService
{
    public function getAccountIndexData(?string $search, int $perPage = 15): array
    {
        $accounts = $this->paginateAccounts($search, $perPage);

        return [
            'headers' => $this->getIndexHeaders(),
            'rows' => $this->buildAccountRows($accounts),
            'accounts' => $accounts,
            'search' => $search ?? '',
        ];
    }

    public function paginateAccounts(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Account::query()
            ->with(['company', 'bank'])
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

    public function getIndexHeaders(): array
    {
        return [
            '#',
            __('Name'),
            __('Account Number'),
            __('Componay'),
            __('Type'),
            __('Bank'),
            __('Balance'),
            __('Active'),
            __('Created At'),
        ];
    }

    public function buildAccountRows(LengthAwarePaginator $accounts): Collection
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
                    $account->company->name ?? __('—'),
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
                    'delete' => [
                        'url' => route('accounts.destroy', $account),
                        'confirm' => __('Are you sure you want to delete :account?', ['account' => $account->name]),
                    ],
                ],
            ];
        });
    }

    public function prepareCreateFormData(): array
    {
        return [
            'companies' => Company::orderBy('name')->pluck('name', 'id')->toArray(),
            'banks' => Bank::orderBy('name')->pluck('name', 'id')->toArray(),
            'accountTypeOptions' => collect(AccountType::cases())
                ->mapWithKeys(static fn (AccountType $type): array => [
                    $type->value => Str::headline($type->name),
                ])
                ->toArray(),
        ];
    }

    public function prepareEditFormData(Account $account): array
    {
        return array_merge(
            ['account' => $account],
            $this->prepareCreateFormData()
        );
    }

    public function createAccount(array $data): Account
    {
        return Account::create($data);
    }

    public function updateAccount(Account $account, array $data): Account
    {
        $account->update($data);

        return $account;
    }

    public function deleteAccount(Account $account): void
    {
        $account->delete();
    }

    public function prepareShowData(Account $account, ?string $search, int $perPage = 15): array
    {
        $account->loadMissing(['company', 'bank']);

        $transactions = $this->getAccountTransactions($account, $search, $perPage);

        return [
            'account' => $account,
            'transactions' => $transactions,
            'headers' => $this->getTransactionHeaders(),
            'rows' => $this->buildTransactionRows($transactions),
            'search' => $search ?? '',
            'balanceChartData' => $this->calculateBalanceChartData($account),
            'incomeExpenseData' => $this->getIncomeExpenseData($account),
            'categories' => $this->getCategories($account),
            'transferAccounts' => $this->getTransferAccounts($account),
            'statuses' => $this->getStatusOptions(),
            'clients' => $this->getClients($account),
        ];
    }

    public function getAccountTransactions(Account $account, ?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::where('account_id', $account->id)
            ->with(['company', 'category', 'creator', 'approver', 'relatedAccount'])
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where(static function ($innerQuery) use ($search) {
                    $innerQuery->where('description', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('date', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhereHas('category', static fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('creator', static fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('approver', static fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getTransactionHeaders(): array
    {
        return [
            '#',
            __('Type'),
            __('Transaction Id'),
            __('Amount'),
            __('Category'),
            __('Status'),
            __('Date'),
            __('Description'),
        ];
    }

    public function buildTransactionRows(LengthAwarePaginator $transactions): Collection
    {
        return collect($transactions->items())->map(function (Transaction $transaction, int $index) use ($transactions) {
            $position = ($transactions->firstItem() ?? 1) + $index;

            return [
                'id' => $transaction->id,
                'name' => 'TXN-'.str_pad($transaction->id, 5, '0', STR_PAD_LEFT),
                'cells' => [
                    $position,
                    ucfirst($transaction->type),
                    Str::upper($transaction->transaction_id ?? __('—')),
                    number_format((float) $transaction->amount, 2),
                    $transaction->category->name ?? __('—'),
                    ucfirst($transaction->status ?? 'pending'),
                    $transaction->date?->format('M j, Y'),
                    Str::limit($transaction->description ?? __('—'), 50),
                ],
                'actions' => [
                    'view' => [
                        'url' => route('transactions.show', $transaction),
                    ],
                    'edit' => [
                        'url' => route('transactions.edit', $transaction),
                    ],
                    'delete' => [
                        'url' => route('transactions.destroy', $transaction),
                        'confirm' => __('Are you sure you want to delete this transaction?'),
                    ],
                ],
            ];
        });
    }

    public function calculateBalanceChartData(Account $account): Collection
    {
        $balanceHistory = Transaction::where('account_id', $account->id)
            ->where('date', '>=', now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(CASE WHEN type = "income" THEN amount WHEN type = "expense" THEN -amount WHEN type = "transfer" THEN -amount ELSE 0 END) as net_change')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $transferInHistory = Transaction::where('related_account_id', $account->id)
            ->where('date', '>=', now()->subMonths(12))
            ->where('type', 'income')
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $mergedHistory = $balanceHistory->map(function ($item) use ($transferInHistory) {
            $transferIn = $transferInHistory[$item->month] ?? 0;
            $item->net_change = (float) $item->net_change + (float) $transferIn;

            return $item;
        });

        $transactionsBefore = Transaction::where('account_id', $account->id)
            ->where('date', '<', now()->subMonths(12))
            ->get();

        $startingBalance = (float) $account->opening_balance;

        foreach ($transactionsBefore as $txn) {
            if ($txn->type === 'income') {
                $startingBalance += (float) $txn->amount;
            } elseif (in_array($txn->type, ['expense', 'transfer'], true)) {
                $startingBalance -= (float) $txn->amount;
            }
        }

        $transfersInBefore = Transaction::where('related_account_id', $account->id)
            ->where('date', '<', now()->subMonths(12))
            ->where('type', 'income')
            ->sum('amount');

        $startingBalance += (float) $transfersInBefore;

        $runningBalance = $startingBalance;

        return $mergedHistory->map(function ($item) use (&$runningBalance) {
            $runningBalance += (float) $item->net_change;

            return [
                'month' => $item->month,
                'balance' => $runningBalance,
            ];
        });
    }

    public function getIncomeExpenseData(Account $account): array
    {
        return Transaction::where('account_id', $account->id)
            ->where('date', '>=', now()->subMonths(12))
            ->whereIn('type', ['income', 'expense'])
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();
    }

    public function getCategories(Account $account): array
    {
        return TransactionCategory::where('company_id', $account->company_id)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static function (TransactionCategory $category): array {
                return [
                    $category->id => $category->name.' ('.$category->type.')',
                ];
            })
            ->toArray();
    }

    public function getTransferAccounts(Account $account): array
    {
        return Account::where('company_id', $account->company_id)
            ->where('id', '!=', $account->id)
            ->with('company')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static function (Account $acc): array {
                $companyName = $acc->company->name ?? 'No Company';

                return [
                    $acc->id => $acc->name.' ('.$companyName.')',
                ];
            })
            ->toArray();
    }

    public function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }

    public function getClients(Account $account): array
    {
        return Client::where('company_id', $account->company_id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
