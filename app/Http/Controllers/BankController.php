<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBankRequest;
use App\Http\Requests\UpdateBankRequest;
use App\Models\Bank;
use App\Services\BankService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankController extends Controller
{
    public function __construct(private BankService $bankService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Bank::class);
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.banks.index', $this->bankService->getIndexData($searchValue));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Bank::class);
        return view('admin.banks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBankRequest $request)
    {
        $this->authorize('create', Bank::class);
        $this->bankService->createBank($request->validated());

        return redirect()->route('banks.index')->with('success', __('Bank created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Bank $bank)
    {
        $this->authorize('view', $bank);

        return view('admin.banks.show', [
            'bank' => $bank,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bank $bank)
    {
        $this->authorize('update', $bank);

        return view('admin.banks.edit', [
            'bank' => $bank,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBankRequest $request, Bank $bank)
    {
        $this->authorize('update', $bank);
        $this->bankService->updateBank($bank, $request->validated());

        return redirect()->route('banks.index')->with('success', __('Bank updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bank $bank)
    {
        $this->authorize('delete', $bank);
        $this->bankService->deleteBank($bank);

        return redirect()->route('banks.index')->with('success', __('Bank deleted successfully.'));
    }
}
