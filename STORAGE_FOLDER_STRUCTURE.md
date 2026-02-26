# Invoice System Storage Folder Structure

## Overview

All invoice-related files are stored in company-specific folders under `storage/app/public/`.

## Folder Structure

```
storage/app/public/
└── companies/
    └── {company_id}/
        ├── invoice/
        │   └── templates/
        │       ├── logos/
        │       │   └── logo_{unique_id}.{ext}
        │       ├── stamps/
        │       │   └── stamp_{unique_id}.{ext}
        │       ├── watermarks/
        │       │   └── watermark_{unique_id}.{ext}
        │       └── signatures/
        │           └── signature_{unique_id}.{ext}
        │
        └── invoices/
            └── {invoice_number}.pdf
            └── INV-2024-0001.pdf
            └── INV-2024-0002.pdf
            └── ...
```

## Detailed Structure

### Template Assets
**Path:** `companies/{company_id}/invoice/templates/{asset_type}/`

- **logos/** - Company logos for invoice templates
- **stamps/** - Company stamps/seals
- **watermarks/** - Watermark images
- **signatures/** - Signature images

**Example:**
```
companies/1/invoice/templates/logos/logo_abc123.png
companies/1/invoice/templates/stamps/stamp_xyz789.png
companies/1/invoice/templates/watermarks/watermark_def456.png
companies/1/invoice/templates/signatures/signature_ghi789.png
```

### Invoice PDFs
**Path:** `companies/{company_id}/invoices/{invoice_number}.pdf`

**Example:**
```
companies/1/invoices/INV-2024-0001.pdf
companies/1/invoices/INV-2024-0002.pdf
companies/2/invoices/INV-2024-0001.pdf
companies/2/invoices/INV-2024-0002.pdf
```

## Code Implementation

### Template Assets Storage
**File:** `app/Services/InvoiceTemplateService.php`

```php
$basePath = "companies/{$companyId}/invoice/templates";

// Logo
$logoPath = $file->storeAs("{$basePath}/logos", uniqid('logo_').'.'.$ext, 'public');

// Stamp
$stampPath = $file->storeAs("{$basePath}/stamps", uniqid('stamp_').'.'.$ext, 'public');

// Watermark
$watermarkPath = $file->storeAs("{$basePath}/watermarks", uniqid('watermark_').'.'.$ext, 'public');

// Signature
$signaturePath = $file->storeAs("{$basePath}/signatures", uniqid('signature_').'.'.$ext, 'public');
```

### Invoice PDF Storage
**File:** `app/Services/PDFGenerationService.php`

```php
$companyId = $invoice->company_id;
$pdfPath = "companies/{$companyId}/invoices/{$invoice->invoice_number}.pdf";

Storage::disk('public')->put($pdfPath, $pdf->output());
```

## Accessing Files

### Template Assets
```php
// Get URL
$logoUrl = Storage::disk('public')->url($template->logo_path);

// Get Path
$logoPath = Storage::disk('public')->path($template->logo_path);
```

### Invoice PDFs
```php
// Get URL
$pdfUrl = Storage::disk('public')->url($invoice->pdf_path);

// Get Path
$pdfPath = Storage::disk('public')->path($invoice->pdf_path);

// Download
return Storage::disk('public')->download($invoice->pdf_path);
```

## Permissions

Ensure the storage directory has proper permissions:

```bash
chmod -R 775 storage/app/public
chown -R www-data:www-data storage/app/public
```

## Notes

1. **Company Isolation:** Each company's files are completely isolated in their own folder
2. **Unique File Names:** Template assets use `uniqid()` to ensure unique filenames
3. **Invoice Numbering:** PDFs are named using the invoice number (e.g., `INV-2024-0001.pdf`)
4. **Public Disk:** All files are stored on the `public` disk for web access
5. **Automatic Cleanup:** When templates are deleted, associated files are automatically removed

