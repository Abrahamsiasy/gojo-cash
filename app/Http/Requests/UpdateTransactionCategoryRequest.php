<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionCategoryRequest extends FormRequest
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
        $user = $this->user();
        $category = $this->route('transactionCategory');

        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($user, $category) {
                    // Regular users can only update categories for their company
                    if ($user && ! $user->hasRole('super-admin') && $user->company_id != $value) {
                        $fail(__('You can only update categories for your company.'));
                    }
                    // Cannot change company if category already exists and user is not super-admin
                    if ($category && $user && ! $user->hasRole('super-admin') && $category->company_id != $value) {
                        $fail(__('You cannot change the company of an existing category.'));
                    }
                },
            ],
            'type' => ['required', 'string', 'in:income,expense'],
            'is_default' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
