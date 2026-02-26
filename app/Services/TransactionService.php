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

class TransactionService extends BaseService
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
            ->forCompany() // Use scope for company filtering
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where(static function ($innerQuery) use ($search) {
                    $innerQuery->where('description', 'like', "%{$search}%")
                        ->orWhereHas('account', static fn ($q) => $q->where('name', 'like', "%{$search}%")
                            ->orWhere('transaction_id', 'like', "%{$search}%")
                            ->orWhere('amount', 'like', "%{$search}%"))
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
                'model' => $transaction, // Include model instance for policy checks
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
            'companies' => $this->getCompaniesForSelect(),
            'accounts' => $this->getAccountsForSelect(),
            'categories' => $this->getCategoriesForSelect(),
            'clients' => $this->getClientsForSelect(),
            'statuses' => $this->getStatusOptions(),
        ];
    }

    public function recordTransaction(array $data): Transaction
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Auto-assign company for non-super-admin users
        if ($user && ! $user->hasRole('super-admin') && ! isset($data['company_id'])) {
            $data['company_id'] = $user->company_id;
        }

        // Additional permission check for security (validation in request should catch this, but this is a safeguard)
        if ($user && ! $user->hasRole('super-admin')) {
            if (! empty($data['is_transfer']) && ! $user->can('create transfer')) {
                throw new \Illuminate\Auth\Access\AuthorizationException(__('You do not have permission to create transfer transactions.'));
            }

            if (empty($data['is_transfer']) && isset($data['transaction_category_id'])) {
                $category = TransactionCategory::find($data['transaction_category_id']);
                if ($category) {
                    $permissionMap = [
                        'income' => 'create income',
                        'expense' => 'create expense',
                    ];
                    $requiredPermission = $permissionMap[$category->type] ?? null;
                    if ($requiredPermission && ! $user->can($requiredPermission)) {
                        throw new \Illuminate\Auth\Access\AuthorizationException(__('You do not have permission to create :type transactions.', ['type' => $category->type]));
                    }
                }
            }
        }

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
        // when delteing it shold undu the balance of the account
        $account = $transaction->account;
        $account->balance += $transaction->amount;
        $account->save();
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
            'client_id' => $data['client_id'],
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
            'client_id' => $data['client_id'],
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

    protected function getTransactionTypes(): array
    {
        $allTypes = ['income', 'expense', 'transfer'];
        $allowedTypes = $this->getAllowedTransactionTypes();

        return collect($allTypes)
            ->filter(static fn ($type) => in_array($type, $allowedTypes, true))
            ->mapWithKeys(static fn ($type) => [$type => Str::headline($type)])
            ->toArray();
    }

    /**
     * Get allowed transaction types based on user permissions.
     *
     * @return array<string>
     */
    protected function getAllowedTransactionTypes(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        // Super-admins have access to all types
        if ($user->hasRole('super-admin')) {
            return ['income', 'expense', 'transfer'];
        }

        $allowedTypes = [];

        if ($user->can('create income')) {
            $allowedTypes[] = 'income';
        }

        if ($user->can('create expense')) {
            $allowedTypes[] = 'expense';
        }

        if ($user->can('create transfer')) {
            $allowedTypes[] = 'transfer';
        }

        return $allowedTypes;
    }

    protected function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }

    /**
     * Get transaction categories for dropdown select, filtered by company and permissions.
     * Super-admins see all categories, others see only their company's categories.
     * Categories are filtered based on user's transaction type permissions.
     */
    protected function getCategoriesForSelect(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $allowedTypes = $this->getAllowedTransactionTypes();

        // If user has no permissions, return empty array
        if (empty($allowedTypes)) {
            return [];
        }

        // Map transaction types to category types
        $categoryTypes = [];
        if (in_array('income', $allowedTypes, true)) {
            $categoryTypes[] = 'income';
        }
        if (in_array('expense', $allowedTypes, true)) {
            $categoryTypes[] = 'expense';
        }

        // If user can only create transfers, they don't need categories
        if (empty($categoryTypes)) {
            return [];
        }

        $query = TransactionCategory::query()
            ->forCompany() // Filter by company
            ->whereIn('type', $categoryTypes)
            ->orderBy('name');

        return $query->get()
            ->mapWithKeys(static function (TransactionCategory $category): array {
                return [
                    $category->id => $category->name.' ('.$category->type.')',
                ];
            })
            ->toArray();
    }

    public function prepareShowData(Transaction $transaction): array
    {
        $transaction->load([
            'company',
            'account',
            'relatedAccount',
            'category',
            'creator',
            'approver',
            'updater',
            'client',
            'attachments.uploader',
        ]);

        return [
            'transaction' => $transaction,
        ];
    }

    /**
     * Store attachments for a transaction.
     */
    public function storeAttachments(Transaction $transaction, array $files): array
    {
        $storedAttachments = [];
        $companyId = $transaction->company_id;
        $transactionId = $transaction->id;
        $basePath = "companies/{$companyId}/transactions/{$transactionId}";

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->timestamp;
            $transactionIdForFile = $transaction->transaction_id ?? $transaction->id;
            $filename = "{$transactionIdForFile}-{$timestamp}.{$extension}";

            // Store file in public disk
            // storeAs returns path relative to disk root (without 'public/' prefix)
            $storedPath = $file->storeAs($basePath, $filename, 'public');

            // Create attachment record
            $attachment = \App\Models\TransactionAttachment::create([
                'transaction_id' => $transaction->id,
                'file_path' => $storedPath,
                'type' => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by' => \Illuminate\Support\Facades\Auth::id(),
                'file_size' => $file->getSize(),
            ]);

            $storedAttachments[] = $attachment;
        }

        return $storedAttachments;
    }

    /**
     * Delete a transaction attachment.
     */
    public function deleteAttachment(\App\Models\TransactionAttachment $attachment): void
    {
        // Delete file from storage (file_path is relative, so we need to check in public disk)
        $fullPath = 'public/'.$attachment->file_path;
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($fullPath)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($fullPath);
        }

        // Also try public disk directly
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($attachment->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->file_path);
        }

        // Delete attachment record
        $attachment->delete();
    }
}
