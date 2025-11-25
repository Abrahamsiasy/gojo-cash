<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionAttachmentRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Models\TransactionAttachment;
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
            $transaction = $this->transactionService->recordTransaction($request->validated());

            // Handle attachments if provided
            $hasAttachments = false;
            if ($request->hasFile('attachments')) {
                try {
                    $this->transactionService->storeAttachments(
                        $transaction,
                        $request->file('attachments')
                    );
                    $hasAttachments = true;
                } catch (Throwable $e) {
                    report($e);
                    // Transaction is saved, but attachments failed - continue with success message
                }
            }

            // If attachments were uploaded, redirect to show page to view them
            if ($hasAttachments) {
                return redirect()
                    ->route('transactions.show', $transaction)
                    ->with('success', __('Transaction recorded successfully with attachments.'));
            }
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

    /**
     * Store attachments for a transaction.
     */
    public function storeAttachments(StoreTransactionAttachmentRequest $request, Transaction $transaction)
    {
        try {
            $attachments = $this->transactionService->storeAttachments(
                $transaction,
                $request->file('attachments')
            );

            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', __(':count file(s) uploaded successfully.', ['count' => count($attachments)]));
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'error' => __('An error occurred while uploading files. Please try again.'),
            ]);
        }
    }

    /**
     * Delete a transaction attachment.
     */
    public function destroyAttachment(Transaction $transaction, TransactionAttachment $attachment)
    {
        // Verify attachment belongs to transaction
        if ($attachment->transaction_id !== $transaction->id) {
            abort(403);
        }

        $this->transactionService->deleteAttachment($attachment);

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', __('Attachment deleted successfully.'));
    }
}
