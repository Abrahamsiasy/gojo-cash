<?php

namespace App\Services;

use App\Models\Company;
use App\Models\TransactionCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TransactionCategoryService extends BaseService
{
    public function getIndexData(?string $search, int $perPage = 15): array
    {
        $categories = $this->paginateCategories($search, $perPage);

        return [
            'headers' => $this->getIndexHeaders(),
            'rows' => $this->buildCategoryRows($categories),
            'transactionCategories' => $categories,
            'search' => $search ?? '',
        ];
    }

    public function paginateCategories(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return TransactionCategory::query()
            ->with('company')
            ->forCompany() // Use scope for company filtering
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
            __('Company'),
            __('Type'),
            __('Default'),
            __('Created At'),
        ];
    }

    public function buildCategoryRows(LengthAwarePaginator $categories): Collection
    {
        return collect($categories->items())->map(function (TransactionCategory $category, int $index) use ($categories) {
            $position = ($categories->firstItem() ?? 1) + $index;

            return [
                'id' => $category->id,
                'name' => $category->name,
                'model' => $category, // Include model instance for policy checks
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
    }

    public function prepareCreateFormData(): array
    {
        return [
            'companies' => $this->getCompaniesForSelect(),
            'typeOptions' => $this->getTypeOptions(),
        ];
    }

    public function prepareEditFormData(TransactionCategory $transactionCategory): array
    {
        return array_merge(
            ['transactionCategory' => $transactionCategory],
            $this->prepareCreateFormData()
        );
    }

    public function createCategory(array $data): TransactionCategory
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Auto-assign company for non-super-admin users
        if ($user && ! $user->hasRole('super-admin') && ! isset($data['company_id'])) {
            $data['company_id'] = $user->company_id;
        }

        return TransactionCategory::create($data);
    }

    public function updateCategory(TransactionCategory $transactionCategory, array $data): TransactionCategory
    {
        $transactionCategory->update($data);

        return $transactionCategory;
    }

    public function deleteCategory(TransactionCategory $transactionCategory): void
    {
        $transactionCategory->delete();
    }

    public function getTypeOptions(): array
    {
        return [
            'income' => __('Income'),
            'expense' => __('Expense'),
        ];
    }
}
