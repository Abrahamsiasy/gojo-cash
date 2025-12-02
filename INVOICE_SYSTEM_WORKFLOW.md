# Invoice System Workflow & Architecture

## Overview

This invoice system is designed for **multi-tenant** finance management where:
- **Each company** has its own isolated data
- **Each company** can have **multiple invoice templates** with different formats
- **Each company** can generate **multiple invoices** (one per transaction or custom)
- **Invoices are stored separately** - one invoice per transaction/request
- **Different invoice types** (expense, income, service, etc.) use different templates

---

## 1. Multi-Tenancy Architecture

### Company Isolation

```
┌─────────────────────────────────────────────────────────────┐
│                    Company A (ID: 1)                         │
├─────────────────────────────────────────────────────────────┤
│  • Invoice Templates (Company A only)                        │
│    - Standard Invoice Template                              │
│    - Payment Receipt Template                               │
│    - Service Invoice Template                               │
│                                                              │
│  • Invoices (Company A only)                                │
│    - INV-2024-0001 (from Transaction #5)                    │
│    - INV-2024-0002 (from Transaction #12)                    │
│    - INV-2024-0003 (custom invoice)                         │
│                                                              │
│  • Transactions (Company A only)                             │
│    - Income transactions                                     │
│    - Expense transactions                                    │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    Company B (ID: 2)                         │
├─────────────────────────────────────────────────────────────┤
│  • Invoice Templates (Company B only)                        │
│    - Sales Invoice Template (different format)              │
│    - Supplier Invoice Template                               │
│                                                              │
│  • Invoices (Company B only)                                │
│    - INV-2024-0001 (from Transaction #3)                    │
│    - INV-2024-0002 (custom invoice)                         │
│                                                              │
│  • Transactions (Company B only)                             │
│    - Income transactions                                     │
│    - Expense transactions                                    │
└─────────────────────────────────────────────────────────────┘
```

### Database Structure

**Invoice Templates Table:**
```sql
invoice_templates
├── id
├── company_id (FK) ← Links to specific company
├── name
├── type (standard, payment_receipt, service, etc.)
├── is_default (one default per company)
├── logo_path, stamp_path, watermark_path
├── custom_css, header_html, footer_html
└── settings (JSON)
```

**Invoices Table:**
```sql
invoices
├── id
├── company_id (FK) ← Links to specific company
├── invoice_template_id (FK) ← Uses company's template
├── invoice_number (unique: INV-2024-0001)
├── invoice_type (standard, payment_receipt, etc.)
├── transaction_id (FK, nullable) ← Links to source transaction
├── items (JSON array)
├── total_amount
├── pdf_path
└── ... (all invoice data)
```

**Key Point:** Every invoice and template has a `company_id` that ensures data isolation.

---

## 2. Invoice Template System (Company-Specific)

### How Templates Work

Each company can create **multiple invoice templates** with different formats:

```
Company A Templates:
├── Template 1: "Standard Invoice" (type: standard)
│   ├── Logo: company-a-logo.png
│   ├── Colors: Blue theme
│   ├── Layout: Standard format
│   └── is_default: true
│
├── Template 2: "Payment Receipt" (type: payment_receipt)
│   ├── Logo: company-a-logo.png
│   ├── Colors: Green theme
│   ├── Layout: Receipt format
│   └── is_default: false
│
└── Template 3: "Service Invoice" (type: service)
    ├── Logo: company-a-logo.png
    ├── Colors: Purple theme
    ├── Layout: Service-specific format
    └── is_default: false

Company B Templates:
├── Template 4: "Sales Invoice" (type: sales)
│   ├── Logo: company-b-logo.png (different logo!)
│   ├── Colors: Red theme (different colors!)
│   ├── Layout: Sales format (different layout!)
│   └── is_default: true
│
└── Template 5: "Supplier Invoice" (type: supplier)
    ├── Logo: company-b-logo.png
    ├── Colors: Orange theme
    ├── Layout: Supplier format
    └── is_default: false
```

### Template Storage

Templates are stored in company-specific folders:
```
storage/app/public/
└── companies/
    ├── 1/ (Company A)
    │   └── invoice/
    │       └── templates/
    │           ├── logos/
    │           ├── stamps/
    │           ├── watermarks/
    │           └── signatures/
    │
    └── 2/ (Company B)
        └── invoice/
            └── templates/
                ├── logos/
                ├── stamps/
                ├── watermarks/
                └── signatures/
```

---

## 3. Complete Invoice Generation Workflow

### Workflow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    STEP 1: Setup Templates                   │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼
        Company Admin creates invoice templates
        (one or more per company, different formats)
                          │
                          ▼
        Each template has:
        - Company-specific branding (logo, colors)
        - Custom HTML/CSS
        - Different invoice types
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│              STEP 2: Generate Invoice                        │
└─────────────────────────────────────────────────────────────┘
                          │
        ┌─────────────────┴─────────────────┐
        │                                     │
        ▼                                     ▼
┌──────────────────┐              ┌──────────────────┐
│ From Transaction │              │  Custom Invoice  │
└──────────────────┘              └──────────────────┘
        │                                     │
        │ User selects transaction            │ User enters data manually
        │ (e.g., Transaction #5)              │ (items, amounts, etc.)
        │                                     │
        ▼                                     ▼
┌─────────────────────────────────────────────────────────────┐
│              STEP 3: Select Template (Optional)              │
└─────────────────────────────────────────────────────────────┘
                          │
        If no template selected:
        → Uses company's default template
                          │
        If template selected:
        → Uses that specific template
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│              STEP 4: Build Invoice Data                      │
└─────────────────────────────────────────────────────────────┘
                          │
        InvoiceGenerationService:
        ├── Extracts data from transaction OR custom data
        ├── Generates unique invoice number (INV-2024-0001)
        ├── Calculates totals (subtotal, tax, discount)
        ├── Links to company and template
        └── Stores all data in invoices table
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│              STEP 5: Generate PDF                            │
└─────────────────────────────────────────────────────────────┘
                          │
        PDFGenerationService:
        ├── Loads template (HTML/CSS)
        ├── Merges invoice data
        ├── Applies company branding
        ├── Converts to PDF
        └── Saves PDF to storage
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│              STEP 6: Store Invoice                            │
└─────────────────────────────────────────────────────────────┘
                          │
        Invoice saved with:
        ├── company_id (isolated to company)
        ├── invoice_template_id (which template was used)
        ├── transaction_id (if from transaction)
        ├── invoice_number (unique: INV-2024-0001)
        ├── All invoice data (items, totals, etc.)
        └── pdf_path (path to generated PDF)
```

---

## 4. How Expenses and Income Are Handled

### Separate Invoices Per Transaction Type

**Important:** The system doesn't automatically separate expenses from income. Instead:

1. **Each transaction** (expense or income) can generate **one invoice**
2. **Invoice type** is determined by:
   - The **template type** selected
   - The **transaction type** (if generated from transaction)
   - Manual selection (if custom invoice)

### Example Scenarios

#### Scenario 1: Income Transaction → Invoice
```
Transaction #5 (Company A)
├── Type: income
├── Amount: $1,000
├── Client: ABC Corp
└── Category: Service Revenue

↓ Generate Invoice

Invoice INV-2024-0001 (Company A)
├── invoice_type: standard (from template)
├── transaction_id: 5 (links to Transaction #5)
├── customer_name: ABC Corp
├── items: [Service Revenue - $1,000]
├── total_amount: $1,000
└── template: "Standard Invoice" (Company A's template)
```

#### Scenario 2: Expense Transaction → Invoice
```
Transaction #12 (Company A)
├── Type: expense
├── Amount: $500
├── Client: Supplier XYZ
└── Category: Office Supplies

↓ Generate Invoice

Invoice INV-2024-0002 (Company A)
├── invoice_type: payment_receipt (different template!)
├── transaction_id: 12 (links to Transaction #12)
├── customer_name: Supplier XYZ
├── items: [Office Supplies - $500]
├── total_amount: $500
└── template: "Payment Receipt" (Company A's template)
```

#### Scenario 3: Custom Invoice (No Transaction)
```
User creates custom invoice (Company A)
├── invoice_type: service (manually selected)
├── transaction_id: null (not from transaction)
├── customer_name: New Client
├── items: [Custom Service - $2,000]
├── total_amount: $2,000
└── template: "Service Invoice" (Company A's template)
```

### Key Points:

1. **One invoice per transaction** - Each transaction can generate one invoice
2. **Different templates for different purposes** - Company can use different templates for income vs expense invoices
3. **Invoice type is flexible** - Can be standard, payment_receipt, service, etc.
4. **All invoices stored separately** - Each invoice is a separate database record

---

## 5. Invoice Numbering System

### Company-Specific Sequential Numbers

```php
// Invoice number format: INV-{YEAR}-{SEQUENCE}
// Example: INV-2024-0001, INV-2024-0002, etc.

// Each company has its own sequence:
Company A:
├── INV-2024-0001 (first invoice in 2024)
├── INV-2024-0002 (second invoice in 2024)
└── INV-2024-0003 (third invoice in 2024)

Company B:
├── INV-2024-0001 (first invoice in 2024 - separate sequence!)
├── INV-2024-0002 (second invoice in 2024)
└── INV-2024-0003 (third invoice in 2024)
```

**Code Location:** `InvoiceGenerationService::generateInvoiceNumber()`

---

## 6. Data Flow Example

### Complete Example: Company A Generates Invoice from Income Transaction

```
1. USER ACTION:
   User (Company A) navigates to "Generate Invoice"
   → Selects Transaction #5 (Income: $1,000 from ABC Corp)
   → Optionally selects "Standard Invoice" template

2. CONTROLLER:
   InvoiceController::store()
   → Validates request
   → Calls InvoiceGenerationService

3. SERVICE LAYER:
   InvoiceGenerationService::generateFromTransaction()
   ├── Gets Transaction #5
   ├── Gets Company A
   ├── Gets template (selected or default)
   ├── Builds invoice data:
   │   ├── company_id: 1 (Company A)
   │   ├── invoice_template_id: 1 (Standard Invoice template)
   │   ├── invoice_number: "INV-2024-0001" (auto-generated)
   │   ├── invoice_type: "standard" (from template)
   │   ├── transaction_id: 5 (links to Transaction #5)
   │   ├── customer_name: "ABC Corp" (from transaction's client)
   │   ├── items: [{"description": "Service", "amount": 1000}]
   │   └── total_amount: 1000
   └── Creates Invoice record

4. PDF GENERATION:
   PDFGenerationService::generatePdf()
   ├── Loads template HTML/CSS
   ├── Merges invoice data
   ├── Applies Company A's branding (logo, colors)
   ├── Converts to PDF
   └── Saves to: storage/app/public/companies/1/invoices/INV-2024-0001.pdf

5. DATABASE STORAGE:
   invoices table:
   ├── id: 1
   ├── company_id: 1 (Company A)
   ├── invoice_template_id: 1 (Standard Invoice)
   ├── invoice_number: "INV-2024-0001"
   ├── transaction_id: 5
   ├── pdf_path: "companies/1/invoices/INV-2024-0001.pdf"
   └── ... (all other invoice data)

6. RESULT:
   User redirected to invoice detail page
   → Can view, download PDF, or print invoice
```

---

## 7. Key Features

### ✅ Multi-Tenancy
- Each company's data is completely isolated
- `forCompany()` scope automatically filters by user's company
- Super-admins can see all companies

### ✅ Flexible Templates
- Each company can have multiple templates
- Different templates for different invoice types
- Custom branding per company (logo, colors, CSS)
- Custom HTML headers/footers

### ✅ Multiple Invoice Types
- Standard Invoice
- Payment Receipt
- Service Invoice
- Sales Invoice
- Supplier Invoice
- Credit Note
- Debit Note
- Pro Forma Invoice

### ✅ Two Generation Methods
1. **From Transaction** - Automatically extracts data from existing transaction
2. **Custom Invoice** - User manually enters all data

### ✅ PDF Generation
- Each invoice generates a PDF
- PDF uses the template's format and branding
- PDF stored in company-specific folder
- Can be downloaded or streamed

### ✅ Invoice Numbering
- Unique per company
- Sequential per year
- Format: `INV-{YEAR}-{SEQUENCE}`

---

## 8. Database Relationships

```
Company (1) ──< (Many) InvoiceTemplate
Company (1) ──< (Many) Invoice
InvoiceTemplate (1) ──< (Many) Invoice
Transaction (1) ──< (0..1) Invoice (optional link)
Client (1) ──< (Many) Invoice (optional link)
User (1) ──< (Many) Invoice (created_by)
```

---

## 9. File Storage Structure

```
storage/app/public/
└── companies/
    ├── 1/ (Company A)
    │   └── invoice/
    │       ├── templates/
    │       │   ├── logos/logo_abc123.png
    │       │   ├── stamps/stamp_xyz789.png
    │       │   ├── watermarks/watermark_def456.png
    │       │   └── signatures/signature_ghi789.png
    │       └── invoices/
    │           ├── INV-2024-0001.pdf
    │           ├── INV-2024-0002.pdf
    │           └── INV-2024-0003.pdf
    │
    └── 2/ (Company B)
        └── invoice/
            ├── templates/
            │   └── ... (Company B's assets)
            └── invoices/
                └── ... (Company B's PDFs)
```

---

## 10. Authorization & Permissions

### Permission-Based Access

Users need permissions to:
- `list invoice` - View invoice list
- `view invoice` - View specific invoice
- `create invoice` - Generate new invoices
- `edit invoice` - Edit invoice (if needed)
- `delete invoice` - Delete invoice

Same for `invoicetemplate` permissions.

### Company Isolation

- Regular users only see their company's invoices/templates
- Super-admins see all companies' invoices/templates
- Policies enforce company-based access control

---

## Summary

**How it works:**
1. Each **company** has its own **invoice templates** (different formats)
2. Each **company** can generate **multiple invoices** (one per transaction or custom)
3. Each **invoice** is stored **separately** in the database
4. Each **invoice** uses a **template** (company-specific format)
5. Each **invoice** generates a **PDF** (stored in company folder)
6. **Expenses and income** are handled by using different templates/types
7. All data is **isolated by company** using `company_id`

**The system is flexible:**
- One company can have multiple templates (for different invoice types)
- One company can generate many invoices (one per transaction or custom)
- Each invoice is independent and stored separately
- Each invoice can use a different template format

