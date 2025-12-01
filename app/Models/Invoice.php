<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'invoice_template_id',
        'invoice_number',
        'invoice_type',
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'client_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'issue_date',
        'due_date',
        'reference_number',
        'items',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'tax_rate',
        'transaction_id',
        'terms_and_conditions',
        'bank_details',
        'notes',
        'amount_in_words',
        'pdf_path',
        'created_by',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'items' => 'array',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'issue_date' => 'date',
            'due_date' => 'date',
            'meta' => 'array',
        ];
    }

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

    /**
     * Get the company that owns the invoice.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the invoice template used for this invoice.
     */
    public function template()
    {
        return $this->belongsTo(InvoiceTemplate::class, 'invoice_template_id');
    }

    /**
     * Get the client associated with this invoice.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the transaction associated with this invoice.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the user who created the invoice.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
