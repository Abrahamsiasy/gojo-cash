<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }} - Preview</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="no-print fixed top-4 right-4 z-50 flex gap-2">
        @if($invoice->exists && $invoice->id)
            <a href="{{ route('invoices.download', $invoice) }}"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                {{ __('Download PDF') }}
            </a>
            <a href="{{ route('invoices.show', $invoice) }}"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium">
                {{ __('Back') }}
            </a>
        @else
            <a href="{{ route('invoice-templates.index') }}"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium">
                {{ __('Back to Templates') }}
            </a>
        @endif
        <button onclick="window.print()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
            {{ __('Print') }}
        </button>
    </div>

    <div class="max-w-4xl mx-auto my-8 bg-white dark:bg-gray-800 shadow-lg p-8">
        @include('invoices.pdf', [
            'invoice' => $invoice,
            'template' => $template,
            'logoUrl' => $logoUrl ?? ($template->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($template->logo_path) : null),
            'stampUrl' => $stampUrl ?? ($template->stamp_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($template->stamp_path) : null),
            'watermarkUrl' => $watermarkUrl ?? ($template->watermark_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($template->watermark_path) : null),
            'signatureUrl' => $signatureUrl ?? ($template->signature_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($template->signature_path) : null),
            'css' => $css ?? '',
        ])
    </div>
</body>
</html>

