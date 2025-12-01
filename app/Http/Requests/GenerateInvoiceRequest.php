<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateInvoiceRequest extends FormRequest
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
                    if ($user && $user->hasRole('super-admin')) {
                        return;
                    }
                    if ($user && $user->company_id != $value) {
                        $fail(__('You can only generate invoices for your company.'));
                    }
                },
            ],
            'invoice_template_id' => [
                'nullable',
                'exists:invoice_templates,id',
                function ($attribute, $value, $fail) use ($companyId) {
                    if ($value && $companyId) {
                        $template = \App\Models\InvoiceTemplate::find($value);
                        if ($template && $template->company_id != $companyId) {
                            $fail(__('The selected template does not belong to the selected company.'));
                        }
                    }
                },
            ],
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
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'invoice_type' => ['nullable', 'string', 'in:standard,proforma,credit_note,recurring,progress'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', function ($attribute, $value, $fail) {
                $issueDate = $this->input('issue_date');
                if ($value && $issueDate && strtotime($value) < strtotime($issueDate)) {
                    $fail(__('The due date must be after or equal to the issue date.'));
                }
            }],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', function ($attribute, $value, $fail) {
                if (empty($value) || count($value) < 1) {
                    $fail(__('At least one item is required for custom invoices.'));
                }
            }],
            'items.*.description' => ['required_with:items', 'string', 'max:500'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:5'],
            'terms_and_conditions' => ['nullable', 'string'],
            'bank_details' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
