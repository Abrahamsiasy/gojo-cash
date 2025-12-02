<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
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
        $client = $this->route('client');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'min:5', 'max:100'],
            'company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($user, $client) {
                    // Regular users can only update clients for their company
                    if ($user && ! $user->hasRole('super-admin') && $user->company_id != $value) {
                        $fail(__('You can only update clients for your company.'));
                    }
                    // Cannot change company if client already exists and user is not super-admin
                    if ($client && $user && ! $user->hasRole('super-admin') && $client->company_id != $value) {
                        $fail(__('You cannot change the company of an existing client.'));
                    }
                },
            ],
        ];
    }
}
