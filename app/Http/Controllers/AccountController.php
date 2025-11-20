<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountController extends Controller
{
    public function __construct(private AccountService $accountService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.accounts.index', $this->accountService->getAccountIndexData($searchValue));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.accounts.create', $this->accountService->prepareCreateFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccountRequest $request)
    {
        $account = $this->accountService->createAccount($request->validated());

        if ($request->boolean('from_company')) {
            return redirect()
                ->route('companies.show', $account->company_id)
                ->with('success', __('Account created successfully.'));
        }

        return redirect()->route('accounts.index')->with('success', __('Account created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Account $account): View
    {
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        $filters = [
            'type' => $request->string('filter_type')->toString() ?: null,
            'status' => $request->string('filter_status')->toString() ?: null,
            'category_id' => $request->integer('filter_category_id') ?: null,
            'client_id' => $request->integer('filter_client_id') ?: null,
            'date_from' => $request->string('filter_date_from')->toString() ?: null,
            'date_to' => $request->string('filter_date_to')->toString() ?: null,
        ];

        // Remove empty filter values
        $filters = array_filter($filters, static fn ($value) => $value !== null && $value !== '');

        return view('admin.accounts.show', $this->accountService->prepareShowData($account, $searchValue, 15, $filters));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account): View
    {
        return view('admin.accounts.edit', $this->accountService->prepareEditFormData($account));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccountRequest $request, Account $account)
    {
        $this->accountService->updateAccount($account, $request->validated());

        return redirect()->route('accounts.index')->with('success', __('Account updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        $this->accountService->deleteAccount($account);

        return redirect()->route('accounts.index')->with('success', __('Account deleted successfully.'));
    }

    /**
     * Export transactions to CSV.
     */
    public function exportTransactions(Request $request, Account $account): StreamedResponse
    {
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        $filters = [
            'type' => $request->string('filter_type')->toString() ?: null,
            'status' => $request->string('filter_status')->toString() ?: null,
            'category_id' => $request->integer('filter_category_id') ?: null,
            'client_id' => $request->integer('filter_client_id') ?: null,
            'date_from' => $request->string('filter_date_from')->toString() ?: null,
            'date_to' => $request->string('filter_date_to')->toString() ?: null,
        ];

        // Remove empty filter values
        $filters = array_filter($filters, static fn ($value) => $value !== null && $value !== '');

        $transactions = $this->accountService->getTransactionsForExport($account, $searchValue, $filters);

        $filename = 'transactions_'.Str::slug($account->name).'_'.now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = static function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers
            fputcsv($file, [
                '#',
                __('Type'),
                __('Transaction ID'),
                __('Amount'),
                __('Category'),
                __('Client'),
                __('Status'),
                __('Date'),
                __('Description'),
                __('Created By'),
                __('Approved By'),
            ]);

            // Data rows
            foreach ($transactions as $index => $transaction) {
                fputcsv($file, [
                    $index + 1,
                    ucfirst($transaction->type),
                    $transaction->transaction_id ?? '',
                    number_format((float) $transaction->amount, 2),
                    $transaction->category->name ?? '',
                    $transaction->client->name ?? '',
                    ucfirst($transaction->status ?? 'pending'),
                    $transaction->date?->format('Y-m-d') ?? '',
                    $transaction->description ?? '',
                    $transaction->creator->name ?? '',
                    $transaction->approver->name ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
