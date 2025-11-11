<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Company;
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
            __('Bank Name'),
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
                    $account->bank_name ?? __('—'),
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
            'bank_name' => ['required', 'string', 'max:255'],
            'balance' => ['required', 'numeric', 'min:0'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
        ]);
        $account = Account::create($validated);

        return redirect()->route('accounts.index')->with('success', __('Account created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account): View
    {
        $account->load('company');

        return view('admin.accounts.show', [
            'account' => $account,
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
        $account->delete();

        return redirect()->route('accounts.index')->with('success', __('Account deleted successfully.'));
    }
}
