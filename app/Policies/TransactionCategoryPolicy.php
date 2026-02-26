<?php

namespace App\Policies;

use App\Models\TransactionCategory;
use App\Models\User;
use App\Policies\Concerns\ChecksCompanyAccess;

class TransactionCategoryPolicy
{
    use ChecksCompanyAccess;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'list transactioncategory');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TransactionCategory $transactionCategory): bool
    {
        return $this->canAccess($user, 'view transactioncategory', $transactionCategory->company_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create transactioncategory');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TransactionCategory $transactionCategory): bool
    {
        return $this->canAccess($user, 'edit transactioncategory', $transactionCategory->company_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TransactionCategory $transactionCategory): bool
    {
        return $this->canAccess($user, 'delete transactioncategory', $transactionCategory->company_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TransactionCategory $transactionCategory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TransactionCategory $transactionCategory): bool
    {
        return false;
    }
}
