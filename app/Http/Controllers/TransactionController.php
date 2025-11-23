<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $transactionService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.transactions.index', $this->transactionService->getIndexData($searchValue));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.transactions.create', $this->transactionService->prepareCreateFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        try {
            $this->transactionService->recordTransaction($request->validated());
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'error' => __('An unexpected error occurred while saving the transaction.'),
            ])->withInput();
        }

        if ($request->boolean('from_account')) {
            return redirect()
                ->route('accounts.show', $request->input('account_id'))
                ->with('success', __('Transaction recorded successfully.'));
        }

        if ($request->boolean('from_company')) {
            return redirect()
                ->route('companies.show', $request->input('company_id'))
                ->with('success', __('Transaction recorded successfully.'));
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', __('Transaction recorded successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): View
    {
        return view('admin.transactions.show', $this->transactionService->prepareShowData($transaction));
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
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $this->transactionService->deleteTransaction($transaction);

        return redirect()->route('transactions.index')->with('success', __('Transaction deleted successfully.'));
    }
}
