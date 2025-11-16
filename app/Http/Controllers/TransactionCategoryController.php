<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionCategoryRequest;
use App\Http\Requests\UpdateTransactionCategoryRequest;
use App\Models\TransactionCategory;
use App\Services\TransactionCategoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionCategoryController extends Controller
{
    public function __construct(private TransactionCategoryService $transactionCategoryService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.transaction_categories.index', $this->transactionCategoryService->getIndexData($searchValue));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.transaction_categories.create', $this->transactionCategoryService->prepareCreateFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionCategoryRequest $request)
    {
        $this->transactionCategoryService->createCategory($request->validated());

        return redirect()->route('transaction-categories.index')
            ->with('success', __('Transaction category created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(TransactionCategory $transactionCategory)
    {
        //
        return view('admin.transaction_categories.show', [
            'transactionCategory' => $transactionCategory,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TransactionCategory $transactionCategory): View
    {
        return view('admin.transaction_categories.edit', $this->transactionCategoryService->prepareEditFormData($transactionCategory));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionCategoryRequest $request, TransactionCategory $transactionCategory)
    {
        $this->transactionCategoryService->updateCategory($transactionCategory, $request->validated());

        return redirect()->route('transaction-categories.index')
            ->with('success', __('Transaction category updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransactionCategory $transactionCategory)
    {
        $this->transactionCategoryService->deleteCategory($transactionCategory);

        return redirect()->route('transaction-categories.index')
            ->with('success', __('Transaction category deleted successfully.'));
    }
}
