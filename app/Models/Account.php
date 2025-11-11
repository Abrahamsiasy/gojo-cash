<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'account_number',
        'account_type',
        'bank_name',
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
