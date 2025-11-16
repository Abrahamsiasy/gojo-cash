<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService
{
    public function getIndexData(?string $search, int $perPage = 15): array
    {
        $transactions = $this->paginateTransactions($search, $perPage);

        return [
            'headers' => $this->getIndexHeaders(),
            'rows' => $this->buildTransactionRows($transactions),
            'transactions' => $transactions,
            'search' => $search ?? '',
        ];
    }

    public function paginateTransactions(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::with(['company', 'account', 'relatedAccount', 'category', 'creator', 'approver'])
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where(static function ($innerQuery) use ($search) {
                    $innerQuery->where('description', 'like', "%{$search}%")
                        ->orWhereHas('account', static fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('company', static fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('category', static fn ($q) => $q->where('name', 'like', "%{$search}%"));
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
            __('Transactino Id'),
            __('Company'),
            __('Account'),
            __('Type'),
            __('Amount'),
            __('Category'),
            __('Status'),
            __('Date'),
            __('Created By'),
            __('Approved By'),
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
                    $transaction->transaction_id ?? __('—'),
                    $transaction->company->name ?? __('—'),
                    $transaction->account->name ?? __('—'),
                    ucfirst($transaction->type),
                    number_format((float) $transaction->amount, 2),
                    $transaction->category->name ?? __('—'),
                    ucfirst($transaction->status ?? 'pending'),
                    $transaction->date?->format('M j, Y'),
                    $transaction->creator->name ?? __('—'),
                    $transaction->approver?->name ?? __('—'),
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

    public function prepareCreateFormData(): array
    {
        return [
            'companies' => Company::orderBy('name')->pluck('name', 'id')->toArray(),
            'accounts' => $this->getAccountsForSelect(),
            'categories' => $this->getCategoryOptions(),
            'transactionTypes' => $this->getTransactionTypes(),
            'statuses' => $this->getStatusOptions(),
        ];
    }

    public function recordTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $data['date'] = $data['date'] ?? now();
            $companyId = $data['company_id'];

            $account = Account::lockForUpdate()->findOrFail($data['account_id']);

            if (! empty($data['is_transfer'])) {
                return $this->handleTransfer($account, $data, $companyId);
            }

            $category = TransactionCategory::findOrFail($data['transaction_category_id']);

            return match ($category->type) {
                'income' => $this->handleIncome($account, $category, $data, $companyId),
                'expense' => $this->handleExpense($account, $category, $data, $companyId),
                default => throw new \InvalidArgumentException(__('Unsupported transaction type.')),
            };
        });
    }

    public function deleteTransaction(Transaction $transaction): void
    {
        $transaction->delete();
    }

    protected function handleIncome(Account $account, TransactionCategory $category, array $data, int $companyId): Transaction
    {
        $previousBalance = $account->balance;
        $newBalance = $previousBalance + $data['amount'];

        $account->update(['balance' => $newBalance]);

        return Transaction::create([
            'company_id' => $companyId,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'transaction_id' => $data['transaction_id'] ?? null,
            'type' => 'income',
            'amount' => $data['amount'],
            'previous_balance' => $previousBalance,
            'new_balance' => $newBalance,
            'description' => $data['description'] ?? null,
            'date' => $data['date'],
            'created_by' => Auth::id(),
        ]);
    }

    protected function handleExpense(Account $account, TransactionCategory $category, array $data, int $companyId): Transaction
    {
        if ($account->balance < $data['amount']) {
            throw new \RuntimeException(__('Insufficient funds in :account.', ['account' => $account->name]));
        }

        $previousBalance = $account->balance;
        $newBalance = $previousBalance - $data['amount'];
        $account->update(['balance' => $newBalance]);

        return Transaction::create([
            'company_id' => $companyId,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'transaction_id' => $data['transaction_id'] ?? null,
            'type' => 'expense',
            'amount' => $data['amount'],
            'previous_balance' => $previousBalance,
            'new_balance' => $newBalance,
            'description' => $data['description'] ?? null,
            'date' => $data['date'],
            'created_by' => Auth::id(),
        ]);
    }

    protected function handleTransfer(Account $sourceAccount, array $data, int $companyId): Transaction
    {
        if (empty($data['related_account_id'])) {
            throw new \RuntimeException(__('Please select a destination account.'));
        }

        $destinationAccount = Account::lockForUpdate()->findOrFail($data['related_account_id']);

        if ($sourceAccount->balance < $data['amount']) {
            throw new \RuntimeException(__('Insufficient funds in :account.', ['account' => $sourceAccount->name]));
        }

        $previousBalanceSource = $sourceAccount->balance;
        $newBalanceSource = $previousBalanceSource - $data['amount'];
        $sourceAccount->update(['balance' => $newBalanceSource]);

        $previousBalanceDest = $destinationAccount->balance;
        $newBalanceDest = $previousBalanceDest + $data['amount'];
        $destinationAccount->update(['balance' => $newBalanceDest]);

        $transfer = Transaction::create([
            'company_id' => $companyId,
            'account_id' => $sourceAccount->id,
            'related_account_id' => $destinationAccount->id,
            'transaction_id' => $data['transaction_id'] ?? null,
            'type' => 'transfer',
            'amount' => $data['amount'],
            'previous_balance' => $previousBalanceSource,
            'new_balance' => $newBalanceSource,
            'description' => $data['description'] ?? "Transfer to {$destinationAccount->name}",
            'date' => $data['date'],
            'created_by' => Auth::id(),
        ]);

        Transaction::create([
            'company_id' => $companyId,
            'account_id' => $destinationAccount->id,
            'related_account_id' => $sourceAccount->id,
            'transaction_id' => $data['transaction_id'] ?? null,
            'type' => 'income',
            'amount' => $data['amount'],
            'previous_balance' => $previousBalanceDest,
            'new_balance' => $newBalanceDest,
            'description' => $data['description'] ?? "Transfer from {$sourceAccount->name}",
            'date' => $data['date'],
            'created_by' => Auth::id(),
        ]);

        return $transfer;
    }

    protected function getAccountsForSelect(): array
    {
        return Account::with('company')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static function (Account $account): array {
                $companyName = $account->company->name ?? 'No Company';

                return [
                    $account->id => $account->name.' ('.$companyName.')',
                ];
            })
            ->toArray();
    }

    protected function getCategoryOptions(): array
    {
        return TransactionCategory::orderBy('name')
            ->get()
            ->mapWithKeys(static function (TransactionCategory $category): array {
                return [
                    $category->id => $category->name.' ('.$category->type.')',
                ];
            })
            ->toArray();
    }

    protected function getTransactionTypes(): array
    {
        return collect(['income', 'expense', 'transfer'])
            ->mapWithKeys(static fn ($type) => [$type => Str::headline($type)])
            ->toArray();
    }

    protected function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }
}
