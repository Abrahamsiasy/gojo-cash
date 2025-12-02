<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Client;
use App\Models\Company;
use App\Models\TransactionCategory;
use Illuminate\Support\Facades\Auth;

abstract class BaseService
{
    /**
     * Get companies for dropdown select.
     * Super-admins see all companies, others see only their company.
     */
    public function getCompaniesForSelect(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->hasRole('super-admin')) {
            return Company::orderBy('name')->pluck('name', 'id')->toArray();
        }

        if ($user && $user->company_id) {
            return Company::where('id', $user->company_id)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();
        }

        return [];
    }

    /**
     * Get accounts for dropdown select, filtered by company.
     * Super-admins see all accounts, others see only their company's accounts.
     */
    public function getAccountsForSelect(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $query = Account::query()->forCompany();

        // For super-admin, show company name in label
        if ($user && $user->hasRole('super-admin')) {
            return $query->with('company')
                ->orderBy('name')
                ->get()
                ->mapWithKeys(static function (Account $account): array {
                    $companyName = $account->company->name ?? 'No Company';

                    return [
                        $account->id => $account->name.' ('.$companyName.')',
                    ];
                })
                ->toArray();
        }

        // For regular users, just show account name
        return $query->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Get transaction categories for dropdown select, filtered by company.
     * Super-admins see all categories, others see only their company's categories.
     */
    protected function getCategoriesForSelect(): array
    {
        return TransactionCategory::query()
            ->forCompany() // Filter by company
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static function (TransactionCategory $category): array {
                return [
                    $category->id => $category->name.' ('.$category->type.')',
                ];
            })
            ->toArray();
    }

    /**
     * Get clients for dropdown select, filtered by company.
     * Super-admins see all clients, others see only their company's clients.
     */
    public function getClientsForSelect(): array
    {
        return Client::query()
            ->forCompany() // Filter by company
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
