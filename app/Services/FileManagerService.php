<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FileManagerService extends BaseService
{
    public function getIndexData(?string $search, int $perPage = 15): array
    {
        $transactions = $this->paginateTransactionsWithAttachments($search, $perPage);

        return [
            'headers' => $this->getIndexHeaders(),
            'rows' => $this->buildTransactionRows($transactions),
            'transactions' => $transactions,
            'search' => $search ?? '',
        ];
    }

    public function paginateTransactionsWithAttachments(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::with(['company', 'account', 'relatedAccount', 'category', 'creator', 'approver', 'attachments'])
            ->forCompany() // Use scope for company filtering
            ->has('attachments') // Only transactions with attachments
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
            __('Transaction Id'),
            __('Company'),
            __('Account'),
            __('Type'),
            __('Amount'),
            __('Category'),
            __('Files'),
            __('Date'),
            __('Created By'),
        ];
    }

    public function buildTransactionRows(LengthAwarePaginator $transactions): Collection
    {
        return collect($transactions->items())->map(function (Transaction $transaction, int $index) use ($transactions) {
            $position = ($transactions->firstItem() ?? 1) + $index;
            $fileCount = $transaction->attachments->count();

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
                    $fileCount.' '.__('file(s)'),
                    $transaction->date?->format('M j, Y'),
                    $transaction->creator->name ?? __('—'),
                ],
                'actions' => [
                    'view' => [
                        'url' => route('transactions.show', $transaction),
                    ],
                ],
            ];
        });
    }
}
