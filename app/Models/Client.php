<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'address',
        'company_id',
    ];

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
