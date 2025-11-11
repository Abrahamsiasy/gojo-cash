<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TransactionCategory;

class TransactionCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search');
    
        $transactionCategories = TransactionCategory::query()
            ->when($search->isNotEmpty(), function ($query) use ($search) {
                $term = $search->toString();
                $query->where('name', 'like', '%'.$term.'%');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
    
        $headers = [
            '#',
            __('Name'),
            __('Company'),
            __('Type'),
            __('Default'),
            __('Created At'),
        ];
    
        $rows = collect($transactionCategories->items())->map(function (TransactionCategory $category, int $index) use ($transactionCategories) {
            $position = ($transactionCategories->firstItem() ?? 1) + $index;
    
            return [
                'id' => $category->id,
                'name' => $category->name,
                'cells' => [
                    $position,
                    $category->name,
                    $category->company->name ?? __('—'),
                    ucfirst($category->type),
                    $category->is_default ? __('Yes') : __('No'),
                    $category->created_at?->translatedFormat('M j, Y'),
                ],
                'actions' => [
                    'view' => [
                        'url' => route('transaction-categories.show', $category),
                    ],
                    'edit' => [
                        'url' => route('transaction-categories.edit', $category),
                    ],
                    'delete' => [
                        'url' => route('transaction-categories.destroy', $category),
                        'confirm' => __('Are you sure you want to delete :category?', ['category' => $category->name]),
                    ],
                ],
            ];
        });
    
        return view('admin.transaction_categories.index', [
            'headers' => $headers,
            'rows' => $rows,
            'search' => $search->toString(),
            'transactionCategories' => $transactionCategories,
        ]);
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // Companies: key = id, value = name
        $companies = Company::orderBy('name')->pluck('name', 'id')->toArray();
    
        // Types: key = enum value, value = human-readable name
        $typeOptions = [
            'income' => __('Income'),
            'expense' => __('Expense'),
        ];
    
        return view('admin.transaction_categories.create', compact('companies', 'typeOptions'));
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    //array:6 [▼ // app/Http/Controllers/TransactionCategoryController.php:99
    //   "_token" => "S88x8wefSdh5pF4fUzAXqOJwP2gRf53R1lzDqYA2"
    //   "name" => "Kaseem Langley"
    //   "company_id" => "83"
    //   "type" => "income"
    //   "is_default" => "1"
    //   "description" => "Consectetur tempora"

    $validated = $request->validate([
        'name' => 'required|string|max:255|min:3',
        'company_id' => 'required|exists:companies,id',
        'type' => 'required|string|max:255|in:income,expense',
        'is_default' => 'required|boolean',
        'description' => 'nullable|string|max:255',
    ]);

    $validated['slug'] = Str::slug($validated['name']);
    $transactionCategory = TransactionCategory::create($validated);
    $transactionCategory->save();
    return redirect()->route('transaction-categories.index')->with('success', 'Transaction category created successfully.');
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
    public function edit(TransactionCategory $transactionCategory)
    {
        // Companies: key = id, value = name
        $companies = Company::orderBy('name')->pluck('name', 'id')->toArray();
    
        // Type options
        $typeOptions = [
            'income' => __('Income'),
            'expense' => __('Expense'),
        ];
    
        return view('admin.transaction_categories.edit', compact('transactionCategory', 'companies', 'typeOptions'));
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransactionCategory $transactionCategory)
    {
        //
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:3',
            'company_id' => 'required|exists:companies,id',
            'type' => 'required|string|max:255|in:income,expense',
            'is_default' => 'required|boolean',
            'description' => 'nullable|string|max:255',
        ]);
        $transactionCategory->update($validated);
        return redirect()->route('transaction-categories.index')->with('success', 'Transaction category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransactionCategory $transactionCategory)
    {
        //
        $transactionCategory->delete();
        return redirect()->route('transaction-categories.index')->with('success', 'Transaction category deleted successfully.');
    }
}
