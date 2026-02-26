<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportTransactionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Transaction::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'csv_file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'], // 10MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'csv_file.required' => __('Please select a file to import.'),
            'csv_file.file' => __('The uploaded file is not valid.'),
            'csv_file.mimes' => __('The file must be an Excel (.xlsx, .xls) or CSV file.'),
            'csv_file.max' => __('The file size must not exceed 10MB.'),
        ];
    }
}
