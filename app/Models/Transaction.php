<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'transaction_id',
        'account_id',
        'related_account_id',
        'category_id',
        'type',
        'amount',
        'previous_balance',
        'new_balance',
        'date',
        'description',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'updated_by',
        'is_reconciled',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'approved_at' => 'datetime',
        'date' => 'date',
    ];

    // 🔗 Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function relatedAccount()
    {
        return $this->belongsTo(Account::class, 'related_account_id');
    }

    public function category()
    {
        return $this->belongsTo(TransactionCategory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
