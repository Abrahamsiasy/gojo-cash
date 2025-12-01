<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceTemplateRequest;
use App\Http\Requests\UpdateInvoiceTemplateRequest;
use App\Models\InvoiceTemplate;
use App\Services\InvoiceTemplateService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceTemplateController extends Controller
{
    public function __construct(private InvoiceTemplateService $templateService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', InvoiceTemplate::class);
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.invoice-templates.index', $this->templateService->getIndexData($searchValue));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', InvoiceTemplate::class);

        return view('admin.invoice-templates.create', [
            'companies' => $this->templateService->getCompaniesForSelect(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceTemplateRequest $request)
    {
        $this->authorize('create', InvoiceTemplate::class);
        $this->templateService->createTemplate($request->validated());

        return redirect()->route('invoice-templates.index')
            ->with('success', __('Invoice template created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceTemplate $invoiceTemplate): View
    {
        $this->authorize('view', $invoiceTemplate);

        return view('admin.invoice-templates.show', [
            'template' => $invoiceTemplate->load('company'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceTemplate $invoiceTemplate): View
    {
        $this->authorize('update', $invoiceTemplate);

        return view('admin.invoice-templates.edit', [
            'template' => $invoiceTemplate->load('company'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceTemplateRequest $request, InvoiceTemplate $invoiceTemplate)
    {
        $this->authorize('update', $invoiceTemplate);
        $this->templateService->updateTemplate($invoiceTemplate, $request->validated());

        return redirect()->route('invoice-templates.index')
            ->with('success', __('Invoice template updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceTemplate $invoiceTemplate)
    {
        $this->authorize('delete', $invoiceTemplate);
        $this->templateService->deleteTemplate($invoiceTemplate);

        return redirect()->route('invoice-templates.index')
            ->with('success', __('Invoice template deleted successfully.'));
    }

    /**
     * Preview the template with sample data.
     */
    public function preview(InvoiceTemplate $invoiceTemplate): View
    {
        $this->authorize('view', $invoiceTemplate);

        // Generate sample invoice data for preview
        // Create a new instance without persisting to database
        $sampleInvoice = new \App\Models\Invoice;
        $sampleInvoice->fill([
            'invoice_number' => 'INV-2024-0001',
            'invoice_type' => $invoiceTemplate->type,
            'company_name' => $invoiceTemplate->company_name ?? $invoiceTemplate->company->name,
            'company_address' => $invoiceTemplate->company_address,
            'company_phone' => $invoiceTemplate->company_phone,
            'company_email' => $invoiceTemplate->company_email,
            'customer_name' => 'Sample Customer',
            'customer_email' => 'customer@example.com',
            'customer_address' => '123 Sample Street, City, Country',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'items' => [
                [
                    'description' => 'Sample Item 1',
                    'quantity' => 2,
                    'unit_price' => 100.00,
                    'total' => 200.00,
                ],
                [
                    'description' => 'Sample Item 2',
                    'quantity' => 1,
                    'unit_price' => 150.00,
                    'total' => 150.00,
                ],
            ],
            'subtotal' => 350.00,
            'tax_amount' => 35.00,
            'tax_rate' => 10.00,
            'discount_amount' => 0,
            'total_amount' => 385.00,
            'currency' => 'USD',
        ]);

        // Mark as not persisted (so routes won't try to use ID)
        $sampleInvoice->exists = false;

        return view('invoices.preview', [
            'invoice' => $sampleInvoice,
            'template' => $invoiceTemplate,
        ]);
    }
}
