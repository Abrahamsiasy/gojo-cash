<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
        // Company validation: required for non-super-admin, optional for super-admin
        $user = $this->user();
        if ($user && $user->hasRole('super-admin')) {
            $rules['company_id'] = ['nullable', 'exists:companies,id'];
        } else {
            $rules['company_id'] = ['nullable', 'exists:companies,id'];
            // Will be auto-assigned in service if not provided
        }

        return $rules;
    }
}
