<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class TransactionCategory extends Model
{
    use SoftDeletes;

    // id, company_id, name, type (income/expense), is_default, created_at, updated_at
    protected $fillable = ['company_id', 'name', 'type', 'is_default'];

    /**
     * Scope a query to only include records for the authenticated user's company.
     * Super-admins see all records.
     */
    public function scopeForCompany($query, ?int $companyId = null)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->hasRole('super-admin')) {
            return $query; // No filter for super-admin
        }

        return $query->where('company_id', $user->company_id ?? $companyId);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
