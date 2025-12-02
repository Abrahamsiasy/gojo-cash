<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
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
        $transaction = $this->route('transaction');

        return [
            'company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($user, $transaction) {
                    // Regular users can only update transactions for their company
                    if ($user && ! $user->hasRole('super-admin') && $user->company_id != $value) {
                        $fail(__('You can only update transactions for your company.'));
                    }
                    // Cannot change company if transaction already exists and user is not super-admin
                    if ($transaction && $user && ! $user->hasRole('super-admin') && $transaction->company_id != $value) {
                        $fail(__('You cannot change the company of an existing transaction.'));
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
                function ($attribute, $value, $fail) use ($companyId) {
                    if ($value && $companyId) {
                        $category = \App\Models\TransactionCategory::find($value);
                        if ($category && $category->company_id != $companyId) {
                            $fail(__('The selected category does not belong to the selected company.'));
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
                function ($attribute, $value, $fail) use ($companyId) {
                    if ($value && $companyId) {
                        $account = \App\Models\Account::find($value);
                        if ($account && $account->company_id != $companyId) {
                            $fail(__('The related account does not belong to the selected company.'));
                        }
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'date' => ['nullable', 'date'],
            'transaction_id' => ['nullable', 'integer'],
            'client_id' => [
                'nullable',
                'exists:clients,id',
                function ($attribute, $value, $fail) use ($companyId) {
                    if ($value && $companyId) {
                        $client = \App\Models\Client::find($value);
                        if ($client && $client->company_id != $companyId) {
                            $fail(__('The selected client does not belong to the selected company.'));
                        }
                    }
                },
            ],
        ];
    }
}
