# Invoice Generation - Explanation

## Two Invoice Generation Modes

The system supports two ways to generate invoices:

### 1. **From Transaction** 
This mode automatically creates an invoice from an existing transaction (expense or income).

**How it works:**
- You select a company and an existing transaction
- The system automatically:
  - Uses the transaction's amount as the invoice amount
  - Uses the transaction's client/customer (if available)
  - Uses the transaction date as the issue date
  - Creates a single invoice item from the transaction
  - Links the invoice to the original transaction

**Use case:** When you want to quickly generate an invoice for a transaction that's already in your system.

**Fields required:**
- Company
- Transaction

**Optional:**
- Invoice Template (uses default if not selected)

---

### 2. **Custom Invoice**
This mode lets you create a completely custom invoice from scratch with full control.

**How it works:**
- You manually enter all invoice details:
  - Invoice items (multiple items with descriptions, quantities, prices)
  - Customer information
  - Dates (issue date, due date)
  - Tax rate and discounts
  - Terms and conditions
  - Notes

**Use case:** When you need to create a detailed invoice with multiple line items, or when the invoice doesn't match any existing transaction.

**Fields required:**
- Company
- Issue Date
- At least one invoice item (description, quantity, unit price)

**Optional:**
- Invoice Template (uses default if not selected)
- Customer/Client
- Due Date
- Tax Rate
- Discount Amount
- Terms and Conditions
- Bank Details
- Notes

---

## Invoice Generation Flow

1. **Choose mode** - Select "From Transaction" or "Custom Invoice"
2. **Fill required fields** - Based on the selected mode
3. **Select template** (optional) - Choose an invoice template or use the default
4. **Generate** - System creates the invoice and PDF
5. **Result** - Invoice is saved and PDF is generated automatically

---

## What Gets Generated

Both modes automatically:
- Generate a unique invoice number (format: `INV-YYYY-####`)
- Create a PDF file of the invoice
- Link the invoice to the selected company
- Use the invoice template for formatting and branding
- Store all invoice data in the database

---

## Key Differences

| Feature | From Transaction | Custom Invoice |
|---------|-----------------|----------------|
| **Data Source** | Existing transaction | Manual entry |
| **Items** | Single item (from transaction) | Multiple items (manual) |
| **Customer** | From transaction's client | Manual entry or select client |
| **Amount** | Transaction amount | Calculated from items |
| **Date** | Transaction date | Manual entry |
| **Flexibility** | Limited (quick invoice) | Full control |
| **Use Case** | Quick invoicing | Detailed/Complex invoices |

