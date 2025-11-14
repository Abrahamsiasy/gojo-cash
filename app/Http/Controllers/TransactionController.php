<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $rows = collect($transactions->items())->map(function (Transaction $transaction, int $index) use ($transactions) {
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

        // Transaction categories: key = id, value = "name->type"
        $categories = TransactionCategory::orderBy('name')
            ->get()
            ->mapWithKeys(static function (TransactionCategory $category): array {
                return [
                    $category->id => $category->name.' ('.$category->type.')',
                ];
            })
            ->toArray();

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

        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'account_id' => ['required', 'exists:accounts,id'],
            'transaction_category_id' => ['nullable', 'exists:transaction_categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'is_transfer' => ['nullable', 'boolean'], // checkbox for transfer
            'related_account_id' => ['nullable', 'exists:accounts,id', 'different:account_id'],
            'description' => ['nullable', 'string', 'max:500'],
            'date' => ['nullable', 'date'],
            'transaction_id' => ['nullable', 'integer'],
        ]);

        $validated['date'] = $validated['date'] ?? now();
        $companyId = $validated['company_id'];

        $account = Account::lockForUpdate()->findOrFail($validated['account_id']);

        DB::beginTransaction();

        try {
            // If it's a transfer
            if (! empty($validated['is_transfer'])) {
                $this->handleTransfer($account, 'transfer', $validated, $companyId);
            } else {
                // Otherwise, must have a category
                $category = TransactionCategory::findOrFail($validated['transaction_category_id']);
                $type = $category->type; // infer from category (income/expense)

                match ($type) {
                    'income' => $this->handleIncome($account, $category, $validated, $companyId),
                    'expense' => $this->handleExpense($account, $category, $validated, $companyId),
                };
            }

            DB::commit();

            // Redirect back to account page if coming from account show page
            if ($request->boolean('from_account')) {
                return redirect()
                    ->route('accounts.show', $validated['account_id'])
                    ->with('success', __('Transaction recorded successfully.'));
            }

            if ($request->boolean('from_company')) {
                return redirect()
                    ->route('companies.show', $companyId)
                    ->with('success', __('Transaction recorded successfully.'));
            }

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

    protected function handleIncome($account, $category, $data, $companyId)
    {
        $previousBalance = $account->balance;
        $newBalance = $previousBalance + $data['amount'];

        $account->update(['balance' => $newBalance]);

        Transaction::create([
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

    protected function handleExpense($account, $category, $data, $companyId)
    {
        if ($account->balance < $data['amount']) {
            throw new \Exception(__('Insufficient funds in :account.', ['account' => $account->name]));
        }

        $previousBalance = $account->balance;
        $newBalance = $previousBalance - $data['amount'];
        $account->update(['balance' => $newBalance]);

        Transaction::create([
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

    protected function handleTransfer($sourceAccount, $category, $data, $companyId)
    {
        if (empty($data['related_account_id'])) {
            throw new \Exception(__('Please select a destination account.'));
        }

        $destinationAccount = Account::lockForUpdate()->findOrFail($data['related_account_id']);

        if ($sourceAccount->balance < $data['amount']) {
            throw new \Exception(__('Insufficient funds in :account.', ['account' => $sourceAccount->name]));
        }

        // Deduct from source
        $previousBalanceSource = $sourceAccount->balance;
        $newBalanceSource = $previousBalanceSource - $data['amount'];
        $sourceAccount->update(['balance' => $newBalanceSource]);

        // Add to destination
        $previousBalanceDest = $destinationAccount->balance;
        $newBalanceDest = $previousBalanceDest + $data['amount'];
        $destinationAccount->update(['balance' => $newBalanceDest]);
        // Log transfer (source)
        Transaction::create([
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

        // Log reciprocal transaction (destination)
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
        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', __('Transaction deleted successfully.'));
    }
}
