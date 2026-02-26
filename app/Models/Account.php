<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'account_number',
        'account_type',
        'bank_id',
        'balance',
        'opening_balance',
        'is_active',
        'description',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'balance' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'account_type' => AccountType::class,
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
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
