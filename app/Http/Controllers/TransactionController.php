<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search');

        $transactions = Transaction::with(['company', 'account', 'relatedAccount', 'category', 'creator', 'approver'])
            ->when($search->isNotEmpty(), static function ($query) use ($search) {
                $term = $search->toString();

                $query->where(static function ($query) use ($term) {
                    $query->where('description', 'like', "%{$term}%")
                        ->orWhereHas('account', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('company', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$term}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $headers = [
            '#',
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

        $rows = collect($transactions->items())->map(function (Transaction $transaction, int $index) use ($transactions) {
            $position = ($transactions->firstItem() ?? 1) + $index;

            return [
                'id' => $transaction->id,
                'name' => 'TXN-'.str_pad($transaction->id, 5, '0', STR_PAD_LEFT),
                'cells' => [
                    $position,
                    $transaction->company->name ?? __('—'),
                    $transaction->account->name ?? __('—'),
                    ucfirst($transaction->type),
                    number_format((float) $transaction->amount, 2),
                    $transaction->category->name ?? __('—'),
                    ucfirst($transaction->status),
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

        return view('admin.transactions.index', [
            'headers' => $headers,
            'rows' => $rows,
            'search' => $search->toString(),
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Companies: key = id, value = name
        $companies = Company::orderBy('name')->pluck('name', 'id')->toArray();

        // Accounts: key = id, value = "Account Name (Company)"
        $accounts = Account::with('company')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static function (Account $account): array {
                $companyName = $account->company->name ?? 'No Company';

                return [
                    $account->id => $account->name.' ('.$companyName.')',
                ];
            })
            ->toArray();

        // Transaction categories: key = id, value = name
        $categories = TransactionCategory::orderBy('name')->pluck('name', 'id')->toArray();

        // Transaction types: enum values (income, expense, transfer)
        $transactionTypes = collect(['income', 'expense', 'transfer'])
            ->mapWithKeys(fn ($type) => [$type => Str::headline($type)])
            ->toArray();

        // Status options
        $statuses = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];

        return view('admin.transactions.create', compact(
            'companies',
            'accounts',
            'categories',
            'transactionTypes',
            'statuses'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate input safely
        dd($request->all());
        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'transaction_category_id' => ['required', 'exists:transaction_categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', 'in:income,expense,transfer'],
            'related_account_id' => ['nullable', 'exists:accounts,id', 'different:account_id'],
            'description' => ['nullable', 'string', 'max:500'],
            'date' => ['nullable', 'date'],
        ]);
        // get transaction_category_id from

        // Normalize optional fields
        $validated['date'] = $validated['date'] ?? now();

        // Fetch base models
        $account = Account::lockForUpdate()->findOrFail($validated['account_id']); // lock to prevent race conditions
        $category = TransactionCategory::findOrFail($validated['transaction_category_id']);

        // Optional: start a DB transaction for safety
        DB::beginTransaction();
        dd($validated);

        try {
            // 2. Expense — Money going out
            if ($validated['type'] === 'expense') {
                if ($account->balance < $validated['amount']) {
                    return back()->withErrors([
                        'amount' => __('Insufficient funds. Available balance: :balance', [
                            'balance' => number_format($account->balance, 2),
                        ]),
                    ])->withInput();
                }

                $account->balance -= $validated['amount'];
                $account->save();

                Transaction::create([
                    'account_id' => $account->id,
                    'transaction_category_id' => $category->id,
                    'amount' => $validated['amount'],
                    'type' => 'expense',
                    'description' => $validated['description'] ?? null,
                    'date' => $validated['date'],
                ]);
            }

            // 3. Income — Money coming in
            elseif ($validated['type'] === 'income') {
                $account->balance += $validated['amount'];
                $account->save();

                Transaction::create([
                    'account_id' => $account->id,
                    'transaction_category_id' => $category->id,
                    'amount' => $validated['amount'],
                    'type' => 'income',
                    'description' => $validated['description'] ?? null,
                    'date' => $validated['date'],
                ]);
            }

            // 4. Transfer — Move between two accounts
            elseif ($validated['type'] === 'transfer') {
                if (empty($validated['related_account_id'])) {
                    return back()->withErrors([
                        'related_account_id' => __('Please select a destination account for the transfer.'),
                    ])->withInput();
                }

                $target = Account::lockForUpdate()->findOrFail($validated['related_account_id']);

                if ($account->balance < $validated['amount']) {
                    return back()->withErrors([
                        'amount' => __('Insufficient funds in source account. Available balance: :balance', [
                            'balance' => number_format($account->balance, 2),
                        ]),
                    ])->withInput();
                }

                // Deduct from source
                $account->balance -= $validated['amount'];
                $account->save();

                // Add to destination
                $target->balance += $validated['amount'];
                $target->save();

                // Log both sides of the transfer
                Transaction::create([
                    'account_id' => $account->id,
                    'related_account_id' => $target->id,
                    'transaction_category_id' => $category->id,
                    'amount' => $validated['amount'],
                    'type' => 'transfer',
                    'description' => $validated['description'] ?? "Transfer to {$target->name}",
                    'date' => $validated['date'],
                ]);
            }

            DB::commit();

            // 5. Success message
            return redirect()
                ->route('transactions.index')
                ->with('success', __('Transaction recorded successfully.'));

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors([
                'error' => __('An unexpected error occurred while saving the transaction.'),
            ])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
