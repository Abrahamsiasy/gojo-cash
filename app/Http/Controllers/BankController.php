<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Http\Requests\StoreBankRequest;
use App\Http\Requests\UpdateBankRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search');

        $banks = Bank::query()
            ->when($search->isNotEmpty(), static function ($query) use ($search) {
                $term = $search->toString();

                $query->where(static function ($query) use ($term) {
                    $query->where('name', 'like', '%' . $term . '%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $headers = [
            '#',
            __('Name'),
            __('Status'),
            __('Default'),
            __('Created'),
        ];

        $rows = collect($banks->items())->map(function (Bank $bank, int $index) use ($banks) {
            $position = ($banks->firstItem() ?? 1) + $index;

            return [
                'id' => $bank->id,
                'name' => $bank->name,
                'cells' => [
                    $position,
                    $bank->name,
                    $bank->status ? __('Active') : __('Inactive'),
                    $bank->is_default ? __('Yes') : __('No'),
                    $bank->created_at?->translatedFormat('M j, Y'),
                ],
                'actions' => [
                    'view' => [
                        'url' => route('banks.show', $bank),
                    ],
                    'edit' => [
                        'url' => route('banks.edit', $bank),
                    ],
                    'delete' => [
                        'url' => route('banks.destroy', $bank),
                        'confirm' => __('Are you sure you want to delete :bank?', ['bank' => $bank->name]),
                    ],
                ],
            ];
        });

        return view('admin.banks.index', [
            'headers' => $headers,
            'rows' => $rows,
            'banks' => $banks,
            'search' => $search->toString(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.banks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBankRequest $request)
    {
        $validated = $request->validated();
        Bank::create($validated);
        return redirect()->route('banks.index')->with('success', 'Bank created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bank $bank)
    {
        return view('admin.banks.show', [
            'bank' => $bank,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bank $bank)
    {
        return view('admin.banks.edit', [
            'bank' => $bank,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBankRequest $request, Bank $bank)
    {
        $validated = $request->validated();
        $bank->update($validated);
        return redirect()->route('banks.index')->with('success', 'Bank updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bank $bank)
    {
        $bank->delete();

        return redirect()->route('banks.index')->with('success', 'Bank deleted successfully.');
    }
}
