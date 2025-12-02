<?php

namespace App\Services;

use App\Models\Bank;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BankService
{
    public function getIndexData(?string $search, int $perPage = 15): array
    {
        $banks = $this->paginateBanks($search, $perPage);

        return [
            'headers' => $this->getIndexHeaders(),
            'rows' => $this->buildBankRows($banks),
            'banks' => $banks,
            'search' => $search ?? '',
        ];
    }

    public function paginateBanks(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Bank::query()
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getIndexHeaders(): array
    {
        return [
            '#',
            __('Name'),
            __('Status'),
            __('Default'),
            __('Created'),
        ];
    }

    public function buildBankRows(LengthAwarePaginator $banks): Collection
    {
        return collect($banks->items())->map(function (Bank $bank, int $index) use ($banks) {
            $position = ($banks->firstItem() ?? 1) + $index;

            return [
                'id' => $bank->id,
                'name' => $bank->name,
                'model' => $bank, // Include model instance for policy checks
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
    }

    public function createBank(array $data): Bank
    {
        return Bank::create($data);
    }

    public function updateBank(Bank $bank, array $data): Bank
    {
        $bank->update($data);

        return $bank;
    }

    public function deleteBank(Bank $bank): void
    {
        $bank->delete();
    }
}
