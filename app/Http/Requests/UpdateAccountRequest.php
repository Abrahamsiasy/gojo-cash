<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $account = $this->route('account');

        return [
            'name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($user, $account) {
                    // Regular users can only update accounts for their company
                    if ($user && ! $user->hasRole('super-admin') && $user->company_id != $value) {
                        $fail(__('You can only update accounts for your company.'));
                    }
                    // Cannot change company if account already exists and user is not super-admin
                    if ($account && $user && ! $user->hasRole('super-admin') && $account->company_id != $value) {
                        $fail(__('You cannot change the company of an existing account.'));
                    }
                },
            ],
            'account_type' => [
                'required',
                'string',
                Rule::in(collect(AccountType::cases())->pluck('value')->all()),
            ],
            'bank_id' => ['required', 'exists:banks,id'],
            'balance' => ['required', 'numeric', 'min:0'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
