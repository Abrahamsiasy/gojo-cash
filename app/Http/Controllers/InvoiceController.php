<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateInvoiceRequest;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Services\InvoiceGenerationService;
use App\Services\PDFGenerationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceGenerationService $invoiceService,
        private PDFGenerationService $pdfService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        $invoices = Invoice::query()
            ->forCompany()
            ->with(['template', 'company', 'client'])
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where(static function ($q) use ($searchValue) {
                    $q->where('invoice_number', 'like', "%{$searchValue}%")
                        ->orWhere('customer_name', 'like', "%{$searchValue}%")
                        ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$searchValue}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'search' => $searchValue ?? '',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Invoice::class);

        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        return view('admin.invoices.create', [
            'companies' => $this->invoiceService->getCompaniesForSelect(),
            'clients' => $this->invoiceService->getClientsForSelect(),
            'accounts' => $this->invoiceService->getAccountsForSelect(),
            'transactions' => Transaction::query()
                ->forCompany()
                ->with(['client', 'category', 'account'])
                ->latest()
                ->limit(500)
                ->get()
                ->map(fn ($transaction) => [
                    'id' => $transaction->id,
                    'account_id' => $transaction->account_id,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'date' => $transaction->date?->toDateString(),
                    'client' => $transaction->client ? [
                        'id' => $transaction->client->id,
                        'name' => $transaction->client->name,
                    ] : null,
                ]),
            'templates' => \App\Models\InvoiceTemplate::query()
                ->forCompany()
                ->get(),
        ]);
    }

    /**
     * Generate invoice from transaction or custom data.
     */
    public function store(GenerateInvoiceRequest $request)
    {
        $this->authorize('create', Invoice::class);

        $data = $request->validated();

        try {
            // If transaction_ids is provided, generate from multiple transactions
            if (! empty($data['transaction_ids'])) {
                $transactionIds = is_string($data['transaction_ids'])
                    ? json_decode($data['transaction_ids'], true)
                    : $data['transaction_ids'];

                if (is_array($transactionIds) && ! empty($transactionIds)) {
                    $template = ! empty($data['invoice_template_id'])
                        ? \App\Models\InvoiceTemplate::findOrFail($data['invoice_template_id'])
                        : null;

                    $invoice = $this->invoiceService->generateFromTransactions($transactionIds, $template);
                } else {
                    throw new \RuntimeException(__('At least one transaction must be selected.'));
                }
            } elseif (! empty($data['transaction_id'])) {
                // If single transaction_id is provided, generate from single transaction
                $transaction = Transaction::findOrFail($data['transaction_id']);
                $template = ! empty($data['invoice_template_id'])
                    ? \App\Models\InvoiceTemplate::findOrFail($data['invoice_template_id'])
                    : null;

                $invoice = $this->invoiceService->generateFromTransaction($transaction, $template);
            } else {
                // Generate custom invoice
                $template = ! empty($data['invoice_template_id'])
                    ? \App\Models\InvoiceTemplate::findOrFail($data['invoice_template_id'])
                    : null;

                $invoice = $this->invoiceService->generateCustomInvoice($data, $template);
            }

            return redirect()->route('invoices.show', $invoice)
                ->with('success', __('Invoice generated successfully.'));
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['template', 'company', 'client', 'transaction', 'creator']);

        return view('admin.invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Download the PDF version of the invoice.
     */
    public function download(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load('template');

        // Generate PDF on-demand
        $pdfContent = $this->pdfService->getPdfContent($invoice, $invoice->template);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$invoice->invoice_number.'.pdf"',
        ]);
    }

    /**
     * Stream the PDF version of the invoice.
     */
    public function stream(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load('template');

        // Generate PDF on-demand
        $pdfContent = $this->pdfService->getPdfContent($invoice, $invoice->template);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$invoice->invoice_number.'.pdf"',
        ]);
    }

    /**
     * Preview the invoice as HTML.
     */
    public function preview(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load('template');

        return view('invoices.preview', [
            'invoice' => $invoice,
            'template' => $invoice->template,
        ]);
    }

    /**
     * Print the invoice.
     */
    public function print(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load('template');

        return view('invoices.print', [
            'invoice' => $invoice,
            'template' => $invoice->template,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        // No need to delete PDF file since we don't store them

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', __('Invoice deleted successfully.'));
    }
}
