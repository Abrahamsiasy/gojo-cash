<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

        return view('admin.accounts.show', $this->accountService->prepareShowData($account, $searchValue));
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
}
