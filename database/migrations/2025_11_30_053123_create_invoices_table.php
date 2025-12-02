<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('invoice_template_id')->constrained('invoice_templates')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('invoice_type')->default('standard');

            // Company info (snapshot at time of invoice creation)
            $table->text('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();

            // Customer/Vendor info
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();

            // Invoice details
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->string('reference_number')->nullable();

            // Invoice data (JSON for flexibility)
            $table->json('items'); // Array of invoice line items
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 5)->default('ETB');
            $table->decimal('tax_rate', 5, 2)->nullable(); // Percentage

            // Related transaction (optional - links to existing transaction)
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();

            // Additional fields
            $table->text('terms_and_conditions')->nullable();
            $table->text('bank_details')->nullable();
            $table->text('notes')->nullable();
            $table->string('amount_in_words')->nullable();

            // PDF storage
            $table->string('pdf_path')->nullable(); // Path to generated PDF

            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable(); // Additional metadata

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('company_id');
            $table->index('invoice_template_id');
            $table->index('client_id');
            $table->index('transaction_id');
            $table->index('invoice_number');
            $table->index('issue_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
