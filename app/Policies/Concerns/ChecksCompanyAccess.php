<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksCompanyAccess
{
    /**
     * Check if user can access a resource based on company_id.
     * Super-admins can access anything, others can only access their company's resources.
     */
    protected function canAccessCompany(User $user, ?int $companyId): bool
    {
        // Super-admin bypass
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Check company match
        return $user->company_id === $companyId;
    }

    /**
     * Check if user has a specific permission.
     * Super-admins bypass permission checks.
     */
    protected function hasPermission(User $user, string $permission): bool
    {
        // Super-admin bypass - they have all permissions
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }

    /**
     * Check permission and company access in one call.
     * Super-admins bypass permission checks.
     */
    protected function canAccess(User $user, string $permission, ?int $companyId): bool
    {
        // Super-admin bypass - they can do anything
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if (! $this->hasPermission($user, $permission)) {
            return false;
        }

        return $this->canAccessCompany($user, $companyId);
    }
}
