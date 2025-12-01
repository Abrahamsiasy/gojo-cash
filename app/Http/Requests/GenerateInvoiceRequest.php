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
        $transactionId = $this->input('transaction_id');
        $transactionIds = $this->getTransactionIds();
        $isFromTransaction = ! empty($transactionId) || ! empty($transactionIds);

        return [
            'company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) {
                    $user = $this->user();
                    // Super-admins can generate invoices for any company
                    if ($user && $user->hasRole('super-admin')) {
                        return;
                    }
                    // Regular users must use their own company
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
            'transaction_id' => [
                $isFromTransaction && empty($this->input('transaction_ids')) ? 'required' : 'nullable',
                'exists:transactions,id',
                function ($attribute, $value, $fail) use ($companyId) {
                    if ($value && $companyId) {
                        $transaction = \App\Models\Transaction::find($value);
                        if ($transaction && $transaction->company_id != $companyId) {
                            $fail(__('The selected transaction does not belong to the selected company.'));
                        }
                    }
                },
            ],
            'transaction_ids' => [
                function ($attribute, $value, $fail) use ($companyId, $isFromTransaction) {
                    // Only validate if we're creating from transactions and transaction_id is not set
                    if ($isFromTransaction && empty($this->input('transaction_id'))) {
                        if (empty($value)) {
                            $fail(__('At least one transaction must be selected.'));

                            return;
                        }

                        $ids = is_string($value) ? json_decode($value, true) : $value;
                        if (! is_array($ids) || empty($ids)) {
                            $fail(__('At least one transaction must be selected.'));

                            return;
                        }

                        $transactions = \App\Models\Transaction::whereIn('id', $ids)->get();
                        if ($transactions->count() !== count($ids)) {
                            $fail(__('One or more selected transactions do not exist.'));

                            return;
                        }

                        if ($companyId) {
                            $invalidTransactions = $transactions->filter(fn ($t) => $t->company_id != $companyId);
                            if ($invalidTransactions->isNotEmpty()) {
                                $fail(__('One or more selected transactions do not belong to the selected company.'));
                            }
                        }
                    }
                },
            ],
            'account_id' => [
                $isFromTransaction ? 'required' : 'nullable',
                'exists:accounts,id',
                function ($attribute, $value, $fail) use ($companyId) {
                    if ($value && $companyId) {
                        $account = \App\Models\Account::find($value);
                        if ($account && $account->company_id != $companyId) {
                            $fail(__('The selected account does not belong to the selected company.'));
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
            'issue_date' => [$isFromTransaction ? 'nullable' : 'required', 'date'],
            'due_date' => ['nullable', 'date', function ($attribute, $value, $fail) {
                $issueDate = $this->input('issue_date');
                if ($value && $issueDate && strtotime($value) < strtotime($issueDate)) {
                    $fail(__('The due date must be after or equal to the issue date.'));
                }
            }],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'items' => [$isFromTransaction ? 'nullable' : 'required', 'array', function ($attribute, $value, $fail) use ($isFromTransaction) {
                if (! $isFromTransaction && (empty($value) || count($value) < 1)) {
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

    /**
     * Get transaction IDs from request (handles both JSON string and array).
     */
    protected function getTransactionIds(): array
    {
        $transactionIds = $this->input('transaction_ids');

        if (empty($transactionIds)) {
            return [];
        }

        if (is_string($transactionIds)) {
            $decoded = json_decode($transactionIds, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($transactionIds) ? $transactionIds : [];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert transaction_ids JSON string to array for easier validation
        if ($this->has('transaction_ids') && is_string($this->input('transaction_ids'))) {
            $ids = json_decode($this->input('transaction_ids'), true);
            if (is_array($ids)) {
                $this->merge(['transaction_ids_array' => $ids]);
            }
        }
    }
}
