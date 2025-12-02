<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionCategoryRequest extends FormRequest
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

        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($user) {
                    // Regular users can only create categories for their company
                    if ($user && ! $user->hasRole('super-admin') && $user->company_id != $value) {
                        $fail(__('You can only create categories for your company.'));
                    }
                },
            ],
            'type' => ['required', 'string', 'in:income,expense'],
            'is_default' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
