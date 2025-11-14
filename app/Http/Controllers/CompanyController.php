<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search');

        $companies = Company::query()
            ->when($search->isNotEmpty(), static function ($query) use ($search) {
                $term = $search->toString();

                $query->where(static function ($query) use ($term) {
                    $query->where('name', 'like', '%'.$term.'%')
                        ->orWhere('slug', 'like', '%'.$term.'%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $headers = [
            '#',
            __('Name'),
            __('Slug'),
            __('Status'),
            __('Trial Ends'),
            __('Created'),
        ];

        $rows = collect($companies->items())->map(function (Company $company, int $index) use ($companies) {
            $position = ($companies->firstItem() ?? 1) + $index;

            return [
                'id' => $company->id,
                'name' => $company->name,
                'cells' => [
                    $position,
                    $company->name,
                    $company->slug,
                    $company->status ? __('Active') : __('Inactive'),
                    optional($company->trial_ends_at)?->translatedFormat('M j, Y') ?? __('—'),
                    $company->created_at?->translatedFormat('M j, Y'),
                ],
                'actions' => [
                    'view' => [
                        'url' => route('companies.show', $company),
                    ],
                    'edit' => [
                        'url' => route('companies.edit', $company),
                    ],
                    'delete' => [
                        'url' => route('companies.destroy', $company),
                        'confirm' => __('Are you sure you want to delete :company?', ['company' => $company->name]),
                    ],
                ],
            ];
        });

        return view('admin.companies.index', [
            'headers' => $headers,
            'rows' => $rows,
            'companies' => $companies,
            'search' => $search->toString(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('admin.companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
            'status' => 'boolean',
            'trial_ends_at' => 'nullable|date',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        Company::create($validated);

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Company $company): View
    {
        $company->loadCount('accounts');

        $search = $request->string('search');

        $accounts = Account::query()
            ->where('company_id', $company->id)
            ->when($search->isNotEmpty(), static function ($query) use ($search) {
                $term = $search->toString();

                $query->where(static function ($subQuery) use ($term) {
                    $subQuery->where('name', 'like', '%'.$term.'%')
                        ->orWhere('account_number', 'like', '%'.$term.'%')
                        ->orWhere('bank_name', 'like', '%'.$term.'%');
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $headers = [
            '#',
            __('Account Name'),
            __('Number'),
            __('Type'),
            __('Bank'),
            __('Balance'),
            __('Active'),
            __('Created'),
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
                    $account->account_type?->value ?? __('—'),
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
                ],
            ];
        });

        $accountStats = Account::query()
            ->selectRaw('COUNT(*) as total_accounts, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_accounts, SUM(balance) as total_balance')
            ->where('company_id', $company->id)
            ->first();

        $transactionStats = Transaction::query()
            ->selectRaw('COUNT(*) as total_transactions, SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income, SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expense')
            ->where('company_id', $company->id)
            ->first();

        $metrics = [
            'total_accounts' => (int) ($accountStats->total_accounts ?? 0),
            'active_accounts' => (int) ($accountStats->active_accounts ?? 0),
            'inactive_accounts' => (int) (($accountStats->total_accounts ?? 0) - ($accountStats->active_accounts ?? 0)),
            'total_balance' => (float) ($accountStats->total_balance ?? 0),
            'total_transactions' => (int) ($transactionStats->total_transactions ?? 0),
            'total_income' => (float) ($transactionStats->total_income ?? 0),
            'total_expense' => (float) ($transactionStats->total_expense ?? 0),
        ];

        $transactionCategories = TransactionCategory::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static function (TransactionCategory $category): array {
                return [
                    $category->id => $category->name.' ('.$category->type.')',
                ];
            })
            ->toArray();

        $companyAccounts = Account::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static function (Account $account): array {
                $companyName = $account->company->name ?? __('—');

                return [
                    $account->id => $account->name.' ('.$companyName.')',
                ];
            })
            ->toArray();

        $transferAccounts = $companyAccounts;

        $statuses = [
            'pending' => __('Pending'),
            'approved' => __('Approved'),
            'rejected' => __('Rejected'),
        ];

        $accountTypeOptions = collect(AccountType::cases())
            ->mapWithKeys(static function (AccountType $type): array {
                return [
                    $type->value => Str::headline($type->name),
                ];
            })
            ->toArray();

        return view('admin.companies.show', [
            'company' => $company,
            'headers' => $headers,
            'rows' => $rows,
            'search' => $search->toString(),
            'accounts' => $accounts,
            'metrics' => $metrics,
            'transactionCategories' => $transactionCategories,
            'companyAccounts' => $companyAccounts,
            'transferAccounts' => $transferAccounts,
            'statuses' => $statuses,
            'accountTypeOptions' => $accountTypeOptions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        //
        return view('admin.companies.edit', [
            'company' => $company,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('companies', 'name')->ignore($company->id)],
            'status' => ['boolean'],
            'trial_ends_at' => ['nullable', 'date'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $company->update($validated);

        return redirect()
            ->route('companies.index')
            ->with('success', __('Company updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        // delete the company
        $company->delete();

        return redirect()->route('companies.index')->with('success', __('Company deleted successfully'));
    }
}
