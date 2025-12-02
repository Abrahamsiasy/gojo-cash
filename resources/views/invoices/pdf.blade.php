<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }

        @page {
            size: {{ $pageSize ?? 'A4' }} {{ $orientation ?? 'portrait' }};
            margin: 15mm;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: white;
        }

        /* Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ddd;
        }

        .company-info {
            flex: 1;
        }

        .company-logo {
            max-width: 120px;
            max-height: 60px;
            margin-bottom: 8px;
        }

        .invoice-info {
            text-align: right;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .invoice-number {
            font-size: 12px;
            color: #666;
        }

        /* Company and Customer Section */
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .company-details,
        .customer-details {
            flex: 1;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
            color: #666;
        }

        .details-content {
            font-size: 11px;
            line-height: 1.5;
        }

        /* Items Table */
        .invoice-items {
            margin-bottom: 20px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead {
            background-color: #f5f5f5;
        }

        .items-table th {
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            border-bottom: 2px solid #ddd;
        }

        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        /* Totals Section */
        .invoice-totals {
            margin-bottom: 15px;
            position: relative;
            text-align: right;
        }

        .totals-table {
            width: 280px;
            margin-left: auto;
            position: relative;
            z-index: 2;
            border-collapse: collapse;
        }

        /* Stamp positioned in the center with transparency */
        .stamp-overlay {
            position: fixed;
            left: 30%;
            top: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            opacity: 0.55;
            pointer-events: none;
        }

        .stamp-overlay img {
            max-width: 150px;
            max-height: 150px;
        }

        .totals-table tr {
            /* Remove any flexbox or complex layouts */
        }

        .totals-table td {
            padding: 5px 10px;
            text-align: left;
            vertical-align: top;
        }

        .totals-table td:first-child {
            width: 60%;
        }

        .totals-table td:last-child {
            width: 40%;
            text-align: right;
            font-weight: normal;
        }

        .totals-table .label {
            font-weight: normal;
            font-size: 11px;
        }

        .totals-table .total-row {
            font-weight: bold;
            font-size: 13px;
            padding-top: 8px;
            border-top: 2px solid #333;
        }

        .totals-table .total-row td {
            padding-top: 8px;
        }

        .totals-table .total-row td:last-child {
            font-weight: bold;
        }


        /* Footer */
        .invoice-footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }

        .footer-section {
            margin-bottom: 10px;
        }

        .footer-title {
            font-weight: bold;
            margin-bottom: 4px;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.1;
            font-size: 80px;
            color: #ccc;
            z-index: -1;
        }

        /* Signature as center watermark - text overlays on it */
        .signature-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
        }

        .signature-watermark img {
            max-width: 400px;
            max-height: 200px;
        }

        /* Content overlay on signature */
        .invoice-container {
            position: relative;
            z-index: 1;
        }

        /* Custom CSS from template */
        {!! $css !!}
    </style>
</head>
<body>
    <!-- Signature as center watermark (behind all content) -->
    @if($signatureUrl)
        <div class="signature-watermark">
            <img src="{{ $signatureUrl }}" alt="Signature Watermark">
        </div>
    @endif

    <!-- Watermark (if different from signature) -->
    @if($watermarkUrl && !$signatureUrl)
        <div class="watermark">
            <img src="{{ $watermarkUrl }}" alt="Watermark" style="max-width: 400px; opacity: 0.1;">
        </div>
    @endif

    <div class="invoice-container">

        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Company Logo" class="company-logo">
                @endif
                <h2 style="font-size: 20px; margin-bottom: 8px;">{{ $invoice->company_name ?? $template->company_name ?? $template->company->name }}</h2>
                @if($invoice->company_address ?? $template->company_address)
                    <div class="details-content">{{ $invoice->company_address ?? $template->company_address }}</div>
                @endif
                @if($invoice->company_phone ?? $template->company_phone)
                    <div class="details-content">Phone: {{ $invoice->company_phone ?? $template->company_phone }}</div>
                @endif
                @if($invoice->company_email ?? $template->company_email)
                    <div class="details-content">Email: {{ $invoice->company_email ?? $template->company_email }}</div>
                @endif
            </div>
            <div class="invoice-info">
                <div class="invoice-title">{{ ucfirst(str_replace('_', ' ', $invoice->invoice_type ?? 'Invoice')) }}</div>
                <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
                @if($invoice->reference_number)
                    <div class="invoice-number">Ref: {{ $invoice->reference_number }}</div>
                @endif
            </div>
        </div>

        <!-- Company and Customer Details -->
        <div class="invoice-details">
            <div class="customer-details">
                <div class="section-title">Bill To:</div>
                <div class="details-content">
                    @if($invoice->customer_name)
                        <strong>{{ $invoice->customer_name }}</strong><br>
                    @endif
                    @if($invoice->customer_address)
                        {{ $invoice->customer_address }}<br>
                    @endif
                    @if($invoice->customer_email)
                        Email: {{ $invoice->customer_email }}<br>
                    @endif
                    @if($invoice->customer_phone)
                        Phone: {{ $invoice->customer_phone }}
                    @endif
                </div>
            </div>
            <div class="company-details">
                <div class="section-title">Invoice Details:</div>
                <div class="details-content">
                    <strong>Issue Date:</strong> {{ $invoice->issue_date->format('F j, Y') }}<br>
                    @if($invoice->due_date)
                        <strong>Due Date:</strong> {{ $invoice->due_date->format('F j, Y') }}<br>
                    @endif
                    <strong>Invoice Type:</strong> {{ ucfirst(str_replace('_', ' ', $invoice->invoice_type)) }}
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="invoice-items">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item['description'] ?? '-' }}</td>
                            <td class="text-right">{{ number_format($item['quantity'] ?? 0, 2) }}</td>
                            <td class="text-right">{{ number_format($item['unit_price'] ?? 0, 2) }} {{ $invoice->currency }}</td>
                            <td class="text-right">{{ number_format($item['total'] ?? (($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)), 2) }} {{ $invoice->currency }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals with Stamp Overlay -->
        <div class="invoice-totals">
            <!-- Stamp positioned under totals -->
            @if($stampUrl)
                <div class="stamp-overlay">
                    <img src="{{ $stampUrl }}" alt="Stamp">
                </div>
            @endif
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="text-right">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                    <tr>
                        <td class="label">Tax ({{ $invoice->tax_rate ?? 0 }}%):</td>
                        <td class="text-right">{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</td>
                    </tr>
                @endif
                @if($invoice->discount_amount > 0)
                    <tr>
                        <td class="label">Discount:</td>
                        <td class="text-right">-{{ number_format($invoice->discount_amount, 2) }} {{ $invoice->currency }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td>Total:</td>
                    <td class="text-right">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @if($invoice->amount_in_words)
                    <tr>
                        <td colspan="2" style="text-align: right; font-style: italic; padding-top: 8px; font-size: 10px;">
                            {{ $invoice->amount_in_words }}
                        </td>
                    </tr>
                @endif
            </table>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            @if($invoice->terms_and_conditions)
                <div class="footer-section">
                    <div class="footer-title">Terms and Conditions:</div>
                    <div>{{ $invoice->terms_and_conditions }}</div>
                </div>
            @endif
            @if($invoice->bank_details)
                <div class="footer-section">
                    <div class="footer-title">Bank Details:</div>
                    <div>{{ $invoice->bank_details }}</div>
                </div>
            @endif
            @if($invoice->notes)
                <div class="footer-section">
                    <div class="footer-title">Notes:</div>
                    <div>{{ $invoice->notes }}</div>
                </div>
            @endif
            <div style="text-align: center; margin-top: 15px; font-size: 9px; color: #999;">
                This is a computer-generated invoice. No signature is required.
            </div>
        </div>
    </div>
</body>
</html>

