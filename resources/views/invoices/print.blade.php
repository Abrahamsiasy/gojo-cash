<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @media print {
            @page {
                size: {{ $template->page_size ?? 'a4' }} {{ $template->orientation ?? 'portrait' }};
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
    @include('invoices.pdf', [
        'invoice' => $invoice,
        'template' => $template,
        'logoUrl' => $template->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($template->logo_path) : null,
        'stampUrl' => $template->stamp_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($template->stamp_path) : null,
        'watermarkUrl' => $template->watermark_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($template->watermark_path) : null,
        'signatureUrl' => $template->signature_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($template->signature_path) : null,
        'css' => $template->custom_css ?? '',
    ])
</head>
<body>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>

