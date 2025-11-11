<?php

namespace App\Http\Controllers;

use App\Models\Company;
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
    public function show(Company $company)
    {
        //
        return view('admin.companies.show', [
            'company' => $company,
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
