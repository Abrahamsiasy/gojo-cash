<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search');

        $accounts = Account::query()
            ->when($search->isNotEmpty(), static function ($query) use ($search) {
                $term = $search->toString();

                $query->where(static function ($query) use ($term) {
                    $query->where('name', 'like', '%'.$term.'%')
                        ->orWhere('account_number', 'like', '%'.$term.'%')
                        ->orWhere('bank_name', 'like', '%'.$term.'%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $headers = [
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

        $rows = collect($accounts->items())->map(function (Account $account, int $index) use ($accounts) {
            $position = ($accounts->firstItem() ?? 1) + $index;

            return [
                'id' => $account->id,
                'name' => $account->name,
                'cells' => [
                    $position,
                    $account->name,
                    $account->account_number ?? __('—'),
                    $account->company->name ?? __('—'),
                    $account->account_type?->value ?? __('—'), // Enum value
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

        return view('admin.accounts.index', [
            'headers' => $headers,
            'rows' => $rows,
            'search' => $search->toString(),
            'accounts' => $accounts, // Pass paginator if needed for links
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Companies: key = id, value = name
        $companies = Company::orderBy('name')->pluck('name', 'id')->toArray();

        // Account types: key = enum value, value = human-readable name
        $accountTypeOptions = collect(AccountType::cases())
            ->mapWithKeys(fn (AccountType $type) => [
                $type->value => Str::headline($type->name), // 'Cash' for 'cash', etc.
            ])
            ->toArray();

        return view('admin.accounts.create', compact('companies', 'accountTypeOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        // dd($request->all());
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'company_id' => ['required', 'exists:companies,id'],
            'account_type' => ['required', 'string', 'max:255'],
            'bank_id' => ['required', 'string', 'max:255'],
            'balance' => ['required', 'numeric', 'min:0'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
        ]);
        $account = Account::create($validated);

        if ($request->boolean('from_company')) {
            return redirect()
                ->route('companies.show', $validated['company_id'])
                ->with('success', __('Account created successfully.'));
        }

        return redirect()->route('accounts.index')->with('success', __('Account created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Account $account): View
    {
        $account->load('company');

        $search = $request->string('search');

        // Get transactions for this account
        // Search works with: description, category.name, type, status, date, amount
        $transactions = Transaction::where('account_id', $account->id)
            ->with(['company', 'category', 'creator', 'approver', 'relatedAccount'])
            ->when($search->isNotEmpty(), static function ($query) use ($search) {
                $term = $search->toString();

                $query->where(static function ($query) use ($term) {
                    $query->where('description', 'like', "%{$term}%")
                        ->orWhere('type', 'like', "%{$term}%")
                        ->orWhere('status', 'like', "%{$term}%")
                        ->orWhere('date', 'like', "%{$term}%")
                        ->orWhere('amount', 'like', "%{$term}%")
                        ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('creator', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('approver', fn ($q) => $q->where('name', 'like', "%{$term}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Prepare transaction table data
        $headers = [
            '#',
            __('Type'),
            __('Transaction Id'),
            __('Amount'),
            __('Category'),
            __('Status'),
            __('Date'),
            __('Description'),
        ];

        $rows = collect($transactions->items())->map(function (Transaction $transaction, int $index) use ($transactions) {
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

        // Get chart data - last 12 months
        $chartData = Transaction::where('account_id', $account->id)
            ->where('date', '>=', now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, type, SUM(amount) as total')
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get();

        // Prepare data for balance over time chart
        // For balance calculation: income adds, expense and transfer (out) subtracts
        $balanceHistory = Transaction::where('account_id', $account->id)
            ->where('date', '>=', now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(CASE WHEN type = "income" THEN amount WHEN type = "expense" THEN -amount WHEN type = "transfer" THEN -amount ELSE 0 END) as net_change')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Also get transfers where this account is the destination (related_account_id)
        $transferInHistory = Transaction::where('related_account_id', $account->id)
            ->where('date', '>=', now()->subMonths(12))
            ->where('type', 'income') // Transfer destination is recorded as income
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Merge transfer ins into balance history
        $mergedHistory = $balanceHistory->map(function ($item) use ($transferInHistory) {
            $transferIn = $transferInHistory[$item->month] ?? 0;
            $item->net_change = (float) $item->net_change + (float) $transferIn;

            return $item;
        });

        // Calculate running balance starting from opening balance
        // Get all transactions before the 12-month period to get starting balance
        $transactionsBefore = Transaction::where('account_id', $account->id)
            ->where('date', '<', now()->subMonths(12))
            ->get();

        $startingBalance = $account->opening_balance;
        foreach ($transactionsBefore as $txn) {
            if ($txn->type === 'income') {
                $startingBalance += (float) $txn->amount;
            } elseif ($txn->type === 'expense' || $txn->type === 'transfer') {
                $startingBalance -= (float) $txn->amount;
            }
        }

        // Also add transfers in from before the period
        $transfersInBefore = Transaction::where('related_account_id', $account->id)
            ->where('date', '<', now()->subMonths(12))
            ->where('type', 'income')
            ->sum('amount');
        $startingBalance += (float) $transfersInBefore;

        $runningBalance = $startingBalance;
        $balanceChartData = $mergedHistory->map(function ($item) use (&$runningBalance) {
            $runningBalance += (float) $item->net_change;

            return [
                'month' => $item->month,
                'balance' => $runningBalance,
            ];
        });

        // Income vs Expense data
        $incomeExpenseData = Transaction::where('account_id', $account->id)
            ->where('date', '>=', now()->subMonths(12))
            ->whereIn('type', ['income', 'expense'])
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // Get data for transaction form (categories, accounts for transfers, statuses)
        $categories = TransactionCategory::where('company_id', $account->company_id)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static function (TransactionCategory $category): array {
                return [
                    $category->id => $category->name.' ('.$category->type.')',
                ];
            })
            ->toArray();

        // Accounts for transfer (excluding current account)
        $transferAccounts = Account::where('company_id', $account->company_id)
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

        // Status options
        $statuses = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];

        return view('admin.accounts.show', [
            'account' => $account,
            'transactions' => $transactions,
            'headers' => $headers,
            'rows' => $rows,
            'search' => $search->toString(),
            'balanceChartData' => $balanceChartData,
            'incomeExpenseData' => $incomeExpenseData,
            'categories' => $categories,
            'transferAccounts' => $transferAccounts,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        // retrun compnies since it shoiuyd use these as well and accountTypeOptions
        $companies = Company::orderBy('name')->pluck('name', 'id')->toArray();
        $accountTypeOptions = collect(AccountType::cases())
            ->mapWithKeys(fn (AccountType $type) => [
                $type->value => Str::headline($type->name),
            ])
            ->toArray();

        return view('admin.accounts.edit', compact('account', 'companies', 'accountTypeOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {

        // dd($request->all());
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_id' => ['required', 'exists:companies,id'],
            'account_number' => ['required', 'string', 'max:255'],
            'account_type' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:255'],
            'balance' => ['required', 'numeric', 'min:0'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
        ]);
        $account->update($validated);

        return redirect()->route('accounts.index')->with('success', __('Account updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        $account->delete(); // make it soft delete so it can be restored

        return redirect()->route('accounts.index')->with('success', __('Account deleted successfully.'));
    }
}
