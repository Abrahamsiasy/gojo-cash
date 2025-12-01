<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreInvoiceTemplateRequest extends FormRequest
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
        $companyId = $this->input('company_id');

        return [
            'company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) {
                    $user = $this->user();
                    // Super-admins can create templates for any company
                    if ($user && $user->hasRole('super-admin')) {
                        return;
                    }
                    // Regular users must use their own company
                    if ($user && $user->company_id != $value) {
                        $fail(__('You can only create templates for your company.'));
                    }
                    // Regular users without a company cannot create templates
                    if ($user && ! $user->company_id) {
                        $fail(__('You must be assigned to a company to create templates.'));
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:standard,proforma,credit_note,recurring,progress'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['sometimes', 'boolean'],
            'logo' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'gif', 'webp'])->max(5120)],
            'stamp' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'gif', 'webp'])->max(5120)],
            'watermark' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'gif', 'webp'])->max(5120)],
            'signature' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])->max(5120)],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'show_qr_code' => ['sometimes', 'boolean'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
