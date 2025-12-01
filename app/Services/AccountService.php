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

class AccountService extends BaseService
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
            ->forCompany() // Use scope for company filtering
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
                'model' => $account, // Include model instance for policy checks
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
            'companies' => $this->getCompaniesForSelect(),
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
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Auto-assign company for non-super-admin users
        if ($user && ! $user->hasRole('super-admin') && ! isset($data['company_id'])) {
            $data['company_id'] = $user->company_id;
        }

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

    public function prepareShowData(Account $account, ?string $search, int $perPage = 15, array $filters = []): array
    {
        $account->loadMissing(['company', 'bank']);

        $transactions = $this->getAccountTransactions($account, $search, $perPage, $filters);

        return [
            'account' => $account,
            'transactions' => $transactions,
            'headers' => $this->getTransactionHeaders(),
            'rows' => $this->buildTransactionRows($transactions),
            'search' => $search ?? '',
            'incomeExpenseChartData' => $this->getIncomeExpenseChartData($account, $filters),
            'kpiData' => $this->getKeyPerformanceIndicators($account, $filters),
            'transactionsByCategoryData' => $this->getTransactionsByCategoryData($account, $filters),
            'categories' => $this->getCategories($account),
            'transferAccounts' => $this->getTransferAccounts($account),
            'statuses' => $this->getStatusOptions(),
            'clients' => $this->getClients($account),
            'typeOptions' => $this->getTypeOptions(),
            'filters' => $filters,
        ];
    }

    public function getKeyPerformanceIndicators(Account $account, array $filters = []): array
    {
        $query = Transaction::where('account_id', $account->id);
        $this->applyFilters($query, $filters);

        // Clone query for different aggregates to avoid interference if we were doing complex joins,
        // but here we can just get the collection since we need to iterate for top category anyway.
        // However, for performance on large datasets, separate DB queries might be better.
        // Let's do separate queries for efficiency.

        // 1. Income & Expense Totals
        $totals = (clone $query)->whereIn('type', ['income', 'expense'])
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $income = (float) ($totals['income'] ?? 0);
        $expense = (float) ($totals['expense'] ?? 0);

        // 2. Top Expense Category
        $topCategory = (clone $query)->where('type', 'expense')
            ->whereNotNull('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();

        return [
            'net_cash_flow' => $income - $expense,
            'total_income' => $income,
            'total_expense' => $expense,
            'top_expense_category' => $topCategory?->category->name ?? __('—'),
            'top_expense_amount' => (float) ($topCategory?->total ?? 0),
        ];
    }

    public function getIncomeExpenseChartData(Account $account, array $filters = []): array
    {
        // Determine date range from filters or default
        $dateFrom = isset($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from']) : now()->subMonths(11)->startOfMonth();
        $dateTo = isset($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to']) : now()->endOfMonth();

        $diffInDays = $dateFrom->diffInDays($dateTo);

        // Dynamic Grouping: < 90 days -> Day, else -> Month
        $groupBy = $diffInDays <= 90 ? 'day' : 'month';
        $dateFormat = $groupBy === 'day' ? '%Y-%m-%d' : '%Y-%m';
        $phpDateFormat = $groupBy === 'day' ? 'Y-m-d' : 'Y-m';
        $labelFormat = $groupBy === 'day' ? 'M j' : 'M Y';

        $query = Transaction::where('account_id', $account->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->whereIn('type', ['income', 'expense']);

        $this->applyFilters($query, \Illuminate\Support\Arr::except($filters, ['date_from', 'date_to']));

        $data = $query->selectRaw("DATE_FORMAT(date, '$dateFormat') as period, type, SUM(amount) as total")
            ->groupBy('period', 'type')
            ->orderBy('period')
            ->get();

        $labels = [];
        $incomeData = [];
        $expenseData = [];

        // Fill gaps
        $current = $dateFrom->copy();
        while ($current <= $dateTo) {
            $key = $current->format($phpDateFormat);
            $label = $current->format($labelFormat);

            $labels[] = $label;

            // Find data for this period
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

    public function getAccountTransactions(Account $account, ?string $search, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Transaction::where('account_id', $account->id)
            ->with(['company', 'category', 'creator', 'approver', 'relatedAccount', 'client'])
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where(static function ($innerQuery) use ($search) {
                    $innerQuery->where('description', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('transaction_id', 'like', "%{$search}%")
                        ->orWhere('date', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhereHas('category', static fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('creator', static fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('approver', static fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(! empty($filters['type'] ?? null), static function ($query) use ($filters) {
                $query->where('type', $filters['type']);
            })
            ->when(! empty($filters['status'] ?? null), static function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(! empty($filters['transaction_id'] ?? null), static function ($query) use ($filters) {
                $query->where('transaction_id', $filters['transaction_id']);
            })
            ->when(! empty($filters['category_id'] ?? null), static function ($query) use ($filters) {
                $query->where('category_id', $filters['category_id']);
            })
            ->when(! empty($filters['client_id'] ?? null), static function ($query) use ($filters) {
                $query->where('client_id', $filters['client_id']);
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
            __('Date & Time'),
            __('Details'),
            __('Category'),
            __('Mode'),
            __('Amount'),
            __('Balance'),
        ];
    }

    public function buildTransactionRows(LengthAwarePaginator $transactions): Collection
    {
        $currentUser = Auth::user();

        return collect($transactions->items())->map(function (Transaction $transaction) use ($currentUser) {
            // Date & Time with optional Transaction ID
            if ($transaction->created_at) {
                $dateTime = $transaction->created_at->format('j M, Y, h:i A');
            } elseif ($transaction->date) {
                $dateTime = $transaction->date->format('j M, Y');
            } else {
                $dateTime = __('—');
            }

            // Append transaction ID if it exists
            if (!empty($transaction->transaction_id)) {
                $dateTime .= ' (ID: ' . $transaction->transaction_id . ')';
            }


            // Details: Format as "(Client Name), Description, by You/by User Name"
            $clientName = $transaction->client?->name ?? '';
            $detailsParts = [];
            if ($clientName) {
                $detailsParts[] = "({$clientName})";
            }
            if ($transaction->description) {
                $detailsParts[] = $transaction->description;
            }
            $createdByName = __('You');
            if ($transaction->creator && $currentUser && $transaction->creator->id !== $currentUser->id) {
                $createdByName = $transaction->creator->name;
            }
            $detailsParts[] = __('by :name', ['name' => $createdByName]);
            $details = implode(', ', $detailsParts) ?: __('—');

            // Category with type
            if ($transaction->category) {
                $categoryName = $transaction->category->name;
                $categoryType = $transaction->category->type ?? $transaction->type ?? 'expense';
                $typeLabel = ucfirst($categoryType);
                $category = [
                    'html' => '<div class="flex flex-col">
                        <span class="text-gray-900 dark:text-gray-100">'.$categoryName.'</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">('.$typeLabel.')</span>
                    </div>',
                ];
            } else {
                $category = __('—');
            }

            // Mode: Get from meta or default to "Cash"
            $meta = $transaction->meta ?? [];
            $mode = $meta['mode'] ?? $meta['payment_mode'] ?? 'Cash';

            // Amount: Format with color - green for income, red for expense
            $amount = (float) $transaction->amount;
            $isIncome = $transaction->type === 'income';
            $amountColor = $isIncome ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';

            // For expenses, display as negative
            $displayAmount = $isIncome ? $amount : -abs($amount);
            $formattedAmount = number_format($displayAmount, 2);

            $amountHtml = '<span class="'.$amountColor.'">'.$formattedAmount.'</span>';

            // Balance: Show new_balance
            $balance = number_format((float) ($transaction->new_balance ?? 0), 2);

            return [
                'id' => $transaction->id,
                'name' => 'TXN-'.str_pad($transaction->id, 5, '0', STR_PAD_LEFT),
                'model' => $transaction, // Include model instance for policy checks
                'cells' => [
                    $dateTime,
                    $details,
                    $category,
                    $mode,
                    ['html' => $amountHtml],
                    $balance,
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

    public function applyFilters($query, array $filters): void
    {
        if (! empty($filters['type'])) {
            $query->where('transactions.type', $filters['type']);
        }
        if (! empty($filters['status'])) {
            $query->where('transactions.status', $filters['status']);
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

    public function getTransactionsByCategoryData(Account $account, array $filters = []): Collection
    {
        $query = Transaction::where('account_id', $account->id);

        $this->applyFilters($query, $filters);

        return $query->whereNotNull('category_id')
            ->join('transaction_categories', 'transactions.category_id', '=', 'transaction_categories.id')
            ->whereNull('transaction_categories.deleted_at')
            ->selectRaw('
                transaction_categories.id as category_id,
                transaction_categories.name as category_name,
                transaction_categories.type as category_type,
                SUM(transactions.amount) as total_amount,
                COUNT(transactions.id) as transaction_count
            ')
            ->groupBy('transaction_categories.id', 'transaction_categories.name', 'transaction_categories.type')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($item) {
                return [
                    'category_id' => $item->category_id,
                    'category_name' => $item->category_name,
                    'category_type' => $item->category_type,
                    'total_amount' => (float) $item->total_amount,
                    'transaction_count' => (int) $item->transaction_count,
                ];
            });
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

    public function getTypeOptions(): array
    {
        return [
            'income' => __('Income'),
            'expense' => __('Expense'),
            'transfer' => __('Transfer'),
        ];
    }

    public function getTransactionsForExport(Account $account, ?string $search, array $filters = []): \Illuminate\Support\Collection
    {
        return Transaction::where('account_id', $account->id)
            ->with(['company', 'category', 'creator', 'approver', 'relatedAccount', 'client'])
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
            ->when(! empty($filters['type'] ?? null), static function ($query) use ($filters) {
                $query->where('type', $filters['type']);
            })
            ->when(! empty($filters['status'] ?? null), static function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(! empty($filters['category_id'] ?? null), static function ($query) use ($filters) {
                $query->where('category_id', $filters['category_id']);
            })
            ->when(! empty($filters['client_id'] ?? null), static function ($query) use ($filters) {
                $query->where('client_id', $filters['client_id']);
            })
            ->when(! empty($filters['date_from'] ?? null), static function ($query) use ($filters) {
                $query->where('date', '>=', $filters['date_from']);
            })
            ->when(! empty($filters['date_to'] ?? null), static function ($query) use ($filters) {
                $query->where('date', '<=', $filters['date_to']);
            })
            ->latest()
            ->get();
    }
}
