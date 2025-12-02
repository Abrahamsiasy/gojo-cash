<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PDFGenerationService
{
    public function generatePdf(Invoice $invoice, InvoiceTemplate $template): string
    {
        // Generate PDF on-demand (no storage needed)
        return $this->getPdfContent($invoice, $template);
    }

    public function getPdfContent(Invoice $invoice, InvoiceTemplate $template): string
    {
        $html = $this->renderInvoiceHtml($invoice, $template);

        // Always use A4 portrait (standard invoice format)
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);

        return $pdf->output();
    }

    protected function renderInvoiceHtml(Invoice $invoice, InvoiceTemplate $template): string
    {
        // Convert images to base64 data URIs for PDF generation
        // DomPDF cannot access URLs, so we need to embed images directly
        $logoUrl = $this->getImageAsDataUri($template->logo_path);
        $stampUrl = $this->getImageAsDataUri($template->stamp_path);
        $watermarkUrl = $this->getImageAsDataUri($template->watermark_path);
        $signatureUrl = $this->getImageAsDataUri($template->signature_path);

        // Page settings - always use A4 portrait (defaults)
        $pageSize = 'A4';
        $orientation = 'portrait';

        // Build CSS (no custom CSS from template)
        $css = $this->buildCss($pageSize, $orientation);

        // Render the invoice view
        return view('invoices.pdf', [
            'invoice' => $invoice,
            'template' => $template,
            'logoUrl' => $logoUrl,
            'stampUrl' => $stampUrl,
            'watermarkUrl' => $watermarkUrl,
            'signatureUrl' => $signatureUrl,
            'pageSize' => $pageSize,
            'orientation' => $orientation,
            'css' => $css,
        ])->render();
    }

    /**
     * Convert image file to base64 data URI for embedding in PDF
     */
    protected function getImageAsDataUri(?string $filePath): ?string
    {
        if (! $filePath || ! Storage::disk('public')->exists($filePath)) {
            return null;
        }

        try {
            $fileContents = Storage::disk('public')->get($filePath);

            // Detect mime type from file extension
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
            ];
            $mimeType = $mimeTypes[$extension] ?? 'image/jpeg';

            $base64 = base64_encode($fileContents);

            return "data:{$mimeType};base64,{$base64}";
        } catch (\Exception $e) {
            // Log error but don't break PDF generation
            Log::warning("Failed to convert image to data URI: {$filePath}", ['error' => $e->getMessage()]);

            return null;
        }
    }

    protected function buildCss(string $pageSize = 'A4', string $orientation = 'portrait'): string
    {
        // Simple CSS with just page settings
        return "@page { size: {$pageSize} {$orientation}; margin: 15mm; }";
    }
}
