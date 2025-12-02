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
        $companyId = $this->input('company_id');
        $user = $this->user();

        return [
            'company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) {
                    $user = $this->user();
                    // Regular users can only create transactions for their company
                    if ($user && ! $user->hasRole('super-admin') && $user->company_id != $value) {
                        $fail(__('You can only create transactions for your company.'));
                    }
                },
            ],
            'account_id' => [
                'required',
                'exists:accounts,id',
                function ($attribute, $value, $fail) use ($companyId) {
                    if ($companyId) {
                        $account = \App\Models\Account::find($value);
                        if ($account && $account->company_id != $companyId) {
                            $fail(__('The selected account does not belong to the selected company.'));
                        }
                    }
                },
            ],
            'transaction_category_id' => [
                Rule::requiredIf(fn () => ! $this->boolean('is_transfer')),
                'nullable',
                'exists:transaction_categories,id',
                function ($attribute, $value, $fail) use ($companyId, $user) {
                    if ($value && $companyId) {
                        $category = \App\Models\TransactionCategory::find($value);
                        if ($category && $category->company_id != $companyId) {
                            $fail(__('The selected category does not belong to the selected company.'));

                            return;
                        }

                        // Validate that user has permission for the category type
                        if ($user && ! $user->hasRole('super-admin')) {
                            $permissionMap = [
                                'income' => 'create income',
                                'expense' => 'create expense',
                            ];

                            $requiredPermission = $permissionMap[$category->type] ?? null;
                            if ($requiredPermission && ! $user->can($requiredPermission)) {
                                $fail(__('You do not have permission to use :type categories.', ['type' => $category->type]));
                            }
                        }
                    }
                },
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'is_transfer' => ['sometimes', 'boolean'],
            'related_account_id' => [
                Rule::requiredIf(fn () => $this->boolean('is_transfer')),
                'nullable',
                'exists:accounts,id',
                'different:account_id',
                function ($attribute, $value, $fail) use ($companyId, $user) {
                    if ($value && $companyId) {
                        $account = \App\Models\Account::find($value);
                        if ($account && $account->company_id != $companyId) {
                            $fail(__('The related account does not belong to the selected company.'));

                            return;
                        }
                    }

                    // Validate transfer permission
                    if ($this->boolean('is_transfer') && $user && ! $user->hasRole('super-admin')) {
                        if (! $user->can('create transfer')) {
                            $fail(__('You do not have permission to create transfer transactions.'));
                        }
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'date' => ['nullable', 'date'],
            'transaction_id' => ['nullable', 'integer'],
            'from_account' => ['sometimes', 'boolean'],
            'from_company' => ['sometimes', 'boolean'],
            'client_id' => [
                'required',
                'exists:clients,id',
                function ($attribute, $value, $fail) use ($companyId) {
                    if ($companyId) {
                        $client = \App\Models\Client::find($value);
                        if ($client && $client->company_id != $companyId) {
                            $fail(__('The selected client does not belong to the selected company.'));
                        }
                    }
                },
            ],
            'attachments' => ['sometimes', 'array', 'max:10'],
            'attachments.*' => [
                File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'])
                    ->max(10240), // 10MB max per file
            ],
        ];
    }
}
