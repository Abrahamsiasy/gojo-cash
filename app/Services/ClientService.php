<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ClientService extends BaseService
{
    public function getClientIndexData(?string $search, int $perPage = 15): array
    {
        $clients = $this->paginateClients($search, $perPage);

        return [
            'headers' => $this->getIndexHeaders(),
            'rows' => $this->buildClientRows($clients),
            'clients' => $clients,
            'search' => $search ?? '',
            'model' => 'Client',
        ];
    }

    public function paginateClients(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Client::query()
            ->with('company')
            ->forCompany() // Use scope for company filtering
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where(static function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhereHas('company', static function ($clientQuery) use ($search) {
                            $clientQuery->where('name', 'like', '%'.$search.'%');
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
            __('Email'),
            __('Company'),
            __('Address'),
            __('Created At'),
        ];
    }

    public function buildClientRows(LengthAwarePaginator $clients): Collection
    {
        return collect($clients->items())->map(function (Client $client, int $index) use ($clients) {
            $position = ($clients->firstItem() ?? 1) + $index;

            return [
                'id' => $client->id,
                'name' => $client->name,
                'model' => $client, // Include model instance for policy checks
                'cells' => [
                    $position,
                    $client->name,
                    $client->email ?? __('—'),
                    $client->company->name ?? __('—'),
                    $client->address ?? __('—'),
                    $client->created_at?->translatedFormat('M j, Y'),
                ],
                'actions' => [
                    'view' => [
                        'url' => route('clients.show', $client),
                    ],
                    'edit' => [
                        'url' => route('clients.edit', $client),
                    ],
                    'delete' => [
                        'url' => route('clients.destroy', $client),
                        'confirm' => __('Are you sure you want to delete :client?', ['client' => $client->name]),
                    ],
                ],
            ];
        });
    }

    public function prepareCreateFormData(): array
    {
        return [
            'companies' => $this->getCompaniesForSelect(),
        ];
    }

    public function createClient(array $data): Client
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Auto-assign company for non-super-admin users
        if ($user && ! $user->hasRole('super-admin') && ! isset($data['company_id'])) {
            $data['company_id'] = $user->company_id;
        }

        return Client::create($data);
    }

    public function prepareEditFormData(Client $client): array
    {
        return array_merge(
            ['client' => $client],
            $this->prepareCreateFormData()
        );
    }

    public function updateClient(Client $client, array $data): Client
    {
        $client->update($data);

        return $client;
    }

    public function deleteClient(Client $client): void
    {
        $client->delete();
    }

    public function prepareShowData(Client $client, ?string $search, int $perPage = 15, array $filters = []): array
    {
        $client->loadMissing(['company']);

        $transactions = $this->getClientTransactions($client, $search, $perPage, $filters);

        return [
            'client' => $client,
            'transactions' => $transactions,
            'headers' => $this->getTransactionHeaders(),
            'rows' => $this->buildTransactionRows($transactions),
            'search' => $search ?? '',
            'stats' => $this->getClientTransactionStats($client, $filters),
            'accounts' => $this->getClientAccounts($client),
            'categories' => $this->getClientCategories($client),
            'filters' => $filters,
        ];
    }

    public function getClientTransactions(Client $client, ?string $search, int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return \App\Models\Transaction::with(['company', 'account', 'relatedAccount', 'category', 'creator', 'approver'])
            ->where('client_id', $client->id)
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where(static function ($innerQuery) use ($search) {
                    $innerQuery->where('description', 'like', "%{$search}%")
                        ->orWhere('transaction_id', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhereHas('account', static fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('company', static fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('category', static fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(! empty($filters['account_id'] ?? null), static function ($query) use ($filters) {
                $query->where('account_id', $filters['account_id']);
            })
            ->when(! empty($filters['category_id'] ?? null), static function ($query) use ($filters) {
                $query->where('category_id', $filters['category_id']);
            })
            ->when(! empty($filters['date_from'] ?? null), static function ($query) use ($filters) {
                $query->where('date', '>=', $filters['date_from']);
            })
            ->when(! empty($filters['date_to'] ?? null), static function ($query) use ($filters) {
                $query->where('date', '<=', $filters['date_to']);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getTransactionHeaders(): array
    {
        return [
            '#',
            __('Date'),
            __('Type'),
            __('Amount'),
            __('Account'),
            __('Category'),
            __('Status'),
            __('Actions'),
        ];
    }

    public function buildTransactionRows(\Illuminate\Contracts\Pagination\LengthAwarePaginator $transactions): Collection
    {
        return collect($transactions->items())->map(function (\App\Models\Transaction $transaction, int $index) use ($transactions) {
            $position = ($transactions->firstItem() ?? 1) + $index;

            return [
                'id' => $transaction->id,
                'name' => $transaction->description ?? __('Transaction #:id', ['id' => $transaction->id]),
                'model' => $transaction, // Include model instance for policy checks
                'cells' => [
                    $position,
                    $transaction->date?->format('M j, Y') ?? __('—'),
                    \Illuminate\Support\Str::headline($transaction->type ?? __('—')),
                    number_format((float) $transaction->amount, 2),
                    $transaction->account->name ?? __('—'),
                    $transaction->category->name ?? __('—'),
                    \Illuminate\Support\Str::headline($transaction->status ?? __('—')),
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

    public function getClientTransactionStats(Client $client, array $filters = []): array
    {
        $query = \App\Models\Transaction::where('client_id', $client->id)
            ->whereIn('type', ['income', 'expense']);

        // Apply filters
        if (! empty($filters['account_id'] ?? null)) {
            $query->where('account_id', $filters['account_id']);
        }
        if (! empty($filters['category_id'] ?? null)) {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['date_from'] ?? null)) {
            $query->where('date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'] ?? null)) {
            $query->where('date', '<=', $filters['date_to']);
        }

        $transactions = $query->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netAmount = $totalIncome - $totalExpense;
        $transactionCount = $transactions->count();

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_amount' => $netAmount,
            'transaction_count' => $transactionCount,
            'income_count' => $transactions->where('type', 'income')->count(),
            'expense_count' => $transactions->where('type', 'expense')->count(),
        ];
    }

    public function getClientAccounts(Client $client): array
    {
        return \App\Models\Transaction::where('client_id', $client->id)
            ->with('account')
            ->whereNotNull('account_id')
            ->get()
            ->pluck('account.name', 'account.id')
            ->unique()
            ->sort()
            ->toArray();
    }

    public function getClientCategories(Client $client): array
    {
        return \App\Models\Transaction::where('client_id', $client->id)
            ->with('category')
            ->whereNotNull('category_id')
            ->get()
            ->pluck('category.name', 'category.id')
            ->unique()
            ->sort()
            ->toArray();
    }
}
