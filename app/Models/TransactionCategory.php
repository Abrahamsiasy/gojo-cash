<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionCategory extends Model
{
    //id, company_id, name, type (income/expense), is_default, created_at, updated_at
    protected $fillable = ['company_id', 'name', 'type', 'is_default'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
    