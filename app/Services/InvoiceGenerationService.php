<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceGenerationService extends BaseService
{
    public function __construct(
        private readonly PDFGenerationService $pdfService
    ) {}

    public function generateFromTransaction(Transaction $transaction, ?InvoiceTemplate $template = null): Invoice
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $company = $transaction->company;

        // Get template or default
        if (! $template) {
            $template = InvoiceTemplate::getDefaultForCompany($company->id);

            if (! $template) {
                throw new \RuntimeException(__('No default invoice template found for this company.'));
            }
        }

        // Build invoice data from transaction
        $invoiceData = $this->buildInvoiceDataFromTransaction($transaction, $company, $template);

        return DB::transaction(function () use ($invoiceData) {
            // Create invoice
            $invoice = Invoice::create($invoiceData);

            // No need to generate PDF at creation - generate on-demand when downloading/printing

            return $invoice->fresh();
        });
    }

    public function generateFromTransactions(array $transactionIds, ?InvoiceTemplate $template = null): Invoice
    {
        if (empty($transactionIds)) {
            throw new \RuntimeException(__('At least one transaction is required.'));
        }

        $transactions = Transaction::whereIn('id', $transactionIds)
            ->with(['client', 'category', 'company'])
            ->get();

        if ($transactions->isEmpty()) {
            throw new \RuntimeException(__('No valid transactions found.'));
        }

        // Ensure all transactions belong to the same company
        $companyIds = $transactions->pluck('company_id')->unique();
        if ($companyIds->count() > 1) {
            throw new \RuntimeException(__('All transactions must belong to the same company.'));
        }

        $company = $transactions->first()->company;

        // Get template or default
        if (! $template) {
            $template = InvoiceTemplate::getDefaultForCompany($company->id);

            if (! $template) {
                throw new \RuntimeException(__('No default invoice template found for this company.'));
            }
        }

        // Build invoice data from multiple transactions
        $invoiceData = $this->buildInvoiceDataFromTransactions($transactions, $company, $template);

        return DB::transaction(function () use ($invoiceData) {
            // Create invoice
            $invoice = Invoice::create($invoiceData);

            return $invoice->fresh();
        });
    }

    public function generateCustomInvoice(array $data, ?InvoiceTemplate $template = null): Invoice
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Auto-assign company for non-super-admin users (if they have one)
        if ($user && ! $user->hasRole('super-admin') && ! isset($data['company_id'])) {
            if ($user->company_id) {
                $data['company_id'] = $user->company_id;
            } else {
                throw new \RuntimeException(__('You must be assigned to a company to generate invoices.'));
            }
        }

        // Company is required for invoice generation
        if (empty($data['company_id'])) {
            throw new \RuntimeException(__('Company is required to generate an invoice.'));
        }

        $company = Company::findOrFail($data['company_id']);

        // Get template or default
        if (! $template) {
            // Try to get template from data first
            if (! empty($data['invoice_template_id'])) {
                $template = InvoiceTemplate::findOrFail($data['invoice_template_id']);
            } else {
                // Fall back to default template
                $template = InvoiceTemplate::getDefaultForCompany($company->id);

                if (! $template) {
                    throw new \RuntimeException(__('No default invoice template found for this company.'));
                }
            }
        }

        // Ensure template belongs to company
        if ($template->company_id != $company->id) {
            throw new \RuntimeException(__('The selected template does not belong to the selected company.'));
        }

        $data['invoice_template_id'] = $template->id;
        $data['created_by'] = $user->id ?? null;

        // Save company information from template (snapshot at time of invoice creation)
        $data['company_name'] = $template->company_name ?? $company->name;
        $data['company_address'] = $template->company_address ?? null;
        $data['company_phone'] = $template->company_phone ?? null;
        $data['company_email'] = $template->company_email ?? null;

        // Set default values for numeric fields (ensure they're never null)
        $data['subtotal'] = isset($data['subtotal']) && $data['subtotal'] !== null ? (float) $data['subtotal'] : 0;
        $data['tax_amount'] = isset($data['tax_amount']) && $data['tax_amount'] !== null ? (float) $data['tax_amount'] : 0;
        $data['discount_amount'] = isset($data['discount_amount']) && $data['discount_amount'] !== null ? (float) $data['discount_amount'] : 0;
        $data['currency'] = $data['currency'] ?? 'ETB';
        $data['tax_rate'] = isset($data['tax_rate']) && $data['tax_rate'] !== null ? (float) $data['tax_rate'] : null;

        // Calculate totals if not provided
        if ($data['subtotal'] == 0 && ! empty($data['items'])) {
            $data['subtotal'] = $this->calculateSubtotal($data['items']);
        }

        // Calculate tax amount from tax rate if not provided
        if ($data['tax_amount'] == 0 && isset($data['tax_rate']) && $data['tax_rate'] > 0) {
            $data['tax_amount'] = ($data['subtotal'] * $data['tax_rate']) / 100;
        }

        if (! isset($data['total_amount']) || $data['total_amount'] == 0) {
            $data['total_amount'] = $data['subtotal'] + $data['tax_amount'] - $data['discount_amount'];
        }

        // Ensure total_amount is never null
        $data['total_amount'] = $data['total_amount'] ?? 0;

        return DB::transaction(function () use ($data, $company) {
            // Generate invoice number inside transaction to ensure proper locking
            if (empty($data['invoice_number'])) {
                $data['invoice_number'] = $this->generateInvoiceNumber($company->id);
            }

            // Create invoice
            $invoice = Invoice::create($data);

            // No need to generate PDF at creation - generate on-demand when downloading/printing

            return $invoice->fresh();
        });
    }

    protected function buildInvoiceDataFromTransaction(Transaction $transaction, Company $company, InvoiceTemplate $template): array
    {
        $client = $transaction->client;

        return [
            'company_id' => $company->id,
            'invoice_template_id' => $template->id,
            'invoice_number' => $this->generateInvoiceNumber($company->id),
            'invoice_type' => $template->type,
            // Save company information from template (snapshot at time of invoice creation)
            'company_name' => $template->company_name ?? $company->name,
            'company_address' => $template->company_address ?? null,
            'company_phone' => $template->company_phone ?? null,
            'company_email' => $template->company_email ?? null,
            'client_id' => $client?->id,
            'customer_name' => $client?->name,
            'customer_email' => $client?->email,
            'customer_phone' => null,
            'customer_address' => $client?->address,
            'issue_date' => $transaction->date ?? now(),
            'due_date' => null,
            'reference_number' => $transaction->transaction_id,
            'items' => [
                [
                    'description' => $transaction->description ?? $transaction->category?->name ?? 'Transaction',
                    'quantity' => 1,
                    'unit_price' => $transaction->amount,
                    'total' => $transaction->amount,
                ],
            ],
            'subtotal' => $transaction->amount,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $transaction->amount,
            'currency' => 'ETB',
            'tax_rate' => null,
            'transaction_id' => $transaction->id,
            'terms_and_conditions' => $template->settings['terms_and_conditions'] ?? null,
            'bank_details' => $template->settings['bank_details'] ?? null,
            'notes' => null,
            'created_by' => Auth::id(),
        ];
    }

    protected function buildInvoiceDataFromTransactions($transactions, Company $company, InvoiceTemplate $template): array
    {
        // Group transactions by client (if they have clients)
        $clients = $transactions->pluck('client')->filter()->unique('id');
        $primaryClient = $clients->first();

        // Build items from all transactions
        $items = $transactions->map(function ($transaction) {
            return [
                'description' => $transaction->description ?? $transaction->category?->name ?? 'Transaction',
                'quantity' => 1,
                'unit_price' => $transaction->amount,
                'total' => $transaction->amount,
            ];
        })->toArray();

        $subtotal = $transactions->sum('amount');
        $totalAmount = $subtotal;

        // Use the earliest transaction date as issue date
        $issueDate = $transactions->min('date') ?? now();

        // Create reference numbers from all transaction IDs
        $referenceNumbers = $transactions->pluck('transaction_id')->filter()->implode(', ');

        return [
            'company_id' => $company->id,
            'invoice_template_id' => $template->id,
            'invoice_number' => $this->generateInvoiceNumber($company->id),
            'invoice_type' => $template->type,
            // Save company information from template (snapshot at time of invoice creation)
            'company_name' => $template->company_name ?? $company->name,
            'company_address' => $template->company_address ?? null,
            'company_phone' => $template->company_phone ?? null,
            'company_email' => $template->company_email ?? null,
            'client_id' => $primaryClient?->id,
            'customer_name' => $primaryClient?->name,
            'customer_email' => $primaryClient?->email,
            'customer_phone' => null,
            'customer_address' => $primaryClient?->address,
            'issue_date' => $issueDate,
            'due_date' => null,
            'reference_number' => $referenceNumbers ?: null,
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $totalAmount,
            'currency' => 'ETB',
            'tax_rate' => null,
            'transaction_id' => $transactions->first()->id, // Store first transaction ID for backward compatibility
            'terms_and_conditions' => $template->settings['terms_and_conditions'] ?? null,
            'bank_details' => $template->settings['bank_details'] ?? null,
            'notes' => null,
            'created_by' => Auth::id(),
        ];
    }

    protected function generateInvoiceNumber(int $companyId): string
    {
        $prefix = 'INV';
        $year = now()->year;

        // Get all invoice numbers for this company in this year
        $invoiceNumbers = Invoice::where('company_id', $companyId)
            ->where('invoice_number', 'like', $prefix.'-'.$year.'-'.$companyId.'-%')
            ->lockForUpdate()
            ->pluck('invoice_number')
            ->toArray();

        $maxSequence = 0;
        // Extract the sequence number from existing invoices
        foreach ($invoiceNumbers as $number) {
            if (preg_match('/'.$prefix.'-\d{4}-'.$companyId.'-(\d+)/', $number, $matches)) {
                $sequence = (int) $matches[1];
                if ($sequence > $maxSequence) {
                    $maxSequence = $sequence;
                }
            }
        }

        $sequence = $maxSequence + 1;

        // Generate the invoice number including company ID with 7 digits
        $invoiceNumber = sprintf('%s-%d-%d-%07d', $prefix, $year, $companyId, $sequence);
        // Safety loop to avoid collisions in rare concurrent requests
        $attempts = 0;
        while (Invoice::where('company_id', $companyId)
            ->where('invoice_number', $invoiceNumber)
            ->exists() && $attempts < 100
        ) {
            $sequence++;
            $invoiceNumber = sprintf('%s-%d-%d-%07d', $prefix, $year, $companyId, $sequence);
            $attempts++;
        }

        return $invoiceNumber;
    }

    protected function calculateSubtotal(array $items): float
    {
        return collect($items)->sum(function ($item) {
            return ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
        });
    }
}
