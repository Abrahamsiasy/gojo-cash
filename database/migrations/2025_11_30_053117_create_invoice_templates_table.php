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
        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('standard'); // standard, proforma, credit_note, recurring, progress
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);

            // Branding
            $table->string('logo_path')->nullable(); // Path to company logo
            $table->string('stamp_path')->nullable(); // Path to company stamp

            // Company header details (can override default company details)
            $table->text('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();

            // Custom HTML sections

            // Styling

            // Optional visual elements
            $table->string('watermark_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->boolean('show_qr_code')->default(false);

            // Settings
            $table->json('settings')->nullable(); // Additional settings in JSON

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('company_id');
            $table->index(['company_id', 'is_default']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_templates');
    }
};
