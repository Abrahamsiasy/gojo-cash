<?php

namespace App\Policies;

use App\Models\InvoiceTemplate;
use App\Models\User;
use App\Policies\Concerns\ChecksCompanyAccess;

class InvoiceTemplatePolicy
{
    use ChecksCompanyAccess;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'list invoicetemplate');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $this->canAccess($user, 'view invoicetemplate', $invoiceTemplate->company_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create invoicetemplate');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $this->canAccess($user, 'edit invoicetemplate', $invoiceTemplate->company_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $this->canAccess($user, 'delete invoicetemplate', $invoiceTemplate->company_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return false;
    }
}
