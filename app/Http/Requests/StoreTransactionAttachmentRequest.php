<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreTransactionAttachmentRequest extends FormRequest
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
        return [
            'attachments' => ['required', 'array', 'min:1', 'max:10'],
            'attachments.*' => [
                'required',
                File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'])
                    ->max(10240), // 10MB max per file
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'attachments.required' => __('Please select at least one file to upload.'),
            'attachments.array' => __('Invalid file format.'),
            'attachments.min' => __('Please select at least one file to upload.'),
            'attachments.max' => __('You can upload a maximum of 10 files at once.'),
            'attachments.*.required' => __('One or more files are missing.'),
            'attachments.*.mimes' => __('Invalid file type. Allowed types: images (jpg, jpeg, png, gif, webp) and documents (pdf, doc, docx, xls, xlsx, csv).'),
            'attachments.*.max' => __('File size must not exceed 10MB.'),
        ];
    }
}
