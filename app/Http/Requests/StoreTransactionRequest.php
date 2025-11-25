<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'exists:companies,id'],
            'account_id' => ['required', 'exists:accounts,id'],
            'transaction_category_id' => [
                Rule::requiredIf(fn () => ! $this->boolean('is_transfer')),
                'nullable',
                'exists:transaction_categories,id',
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'is_transfer' => ['sometimes', 'boolean'],
            'related_account_id' => [
                Rule::requiredIf(fn () => $this->boolean('is_transfer')),
                'nullable',
                'exists:accounts,id',
                'different:account_id',
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'date' => ['nullable', 'date'],
            'transaction_id' => ['nullable', 'integer'],
            'from_account' => ['sometimes', 'boolean'],
            'from_company' => ['sometimes', 'boolean'],
            'client_id' => ['required', 'exists:clients,id'],
            'attachments' => ['sometimes', 'array', 'max:10'],
            'attachments.*' => [
                File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'])
                    ->max(10240), // 10MB max per file
            ],
        ];
    }
}
