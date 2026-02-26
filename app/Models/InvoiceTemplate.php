<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class InvoiceTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'description',
        'is_default',
        'logo_path',
        'stamp_path',
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'watermark_path',
        'signature_path',
        'show_qr_code',
        'settings',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'show_qr_code' => 'boolean',
            'settings' => 'array',
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
     * Get the company that owns the invoice template.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all invoices using this template.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the default template for a company.
     */
    public static function getDefaultForCompany(int $companyId): ?self
    {
        return static::where('company_id', $companyId)
            ->where('is_default', true)
            ->first();
    }
}
