<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\InvoiceTemplate;
use Illuminate\Database\Seeder;

class InvoiceTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies or create a sample one
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $company = Company::create([
                'name' => 'Sample Company',
                'slug' => 'sample-company',
                'status' => true,
            ]);
            $companies = collect([$company]);
        }

        // Create sample templates for each company
        foreach ($companies as $company) {
            // Skip if company already has templates
            if ($company->invoiceTemplates()->count() > 0) {
                continue;
            }

            // 1. Standard Invoice Template (Default)
            InvoiceTemplate::create([
                'company_id' => $company->id,
                'name' => 'Standard Invoice',
                'type' => 'standard',
                'description' => 'Professional standard invoice template for general use',
                'is_default' => true,
                'company_name' => $company->name,
                'company_address' => '123 Business Street, City, State 12345',
                'company_phone' => '+1 (555) 123-4567',
                'company_email' => 'info@'.strtolower(str_replace(' ', '', $company->name)).'.com',
                'show_qr_code' => false,
                'settings' => [
                    'terms_and_conditions' => 'Payment is due within 30 days of invoice date. Late payments may incur a 5% monthly fee.',
                    'bank_details' => 'Bank: Sample Bank\nAccount: 123456789\nRouting: 987654321',
                ],
            ]);

        //     // 2. Proforma Invoice Template
        //     InvoiceTemplate::create([
        //         'company_id' => $company->id,
        //         'name' => 'Proforma Invoice',
        //         'type' => 'proforma',
        //         'description' => 'Quote or estimate template before payment',
        //         'is_default' => false,
        //         'company_name' => $company->name,
        //         'company_address' => '123 Business Street, City, State 12345',
        //         'company_phone' => '+1 (555) 123-4567',
        //         'company_email' => 'info@'.strtolower(str_replace(' ', '', $company->name)).'.com',
        //         'show_qr_code' => false,
        //         'settings' => [
        //             'terms_and_conditions' => 'This is a proforma invoice (quote/estimate). Final invoice will be issued upon confirmation.',
        //         ],
        //     ]);

        //     // 3. Credit Note Template
        //     InvoiceTemplate::create([
        //         'company_id' => $company->id,
        //         'name' => 'Credit Note',
        //         'type' => 'credit_note',
        //         'description' => 'Credit note template for refunds or corrections',
        //         'is_default' => false,
        //         'company_name' => $company->name,
        //         'company_address' => '123 Business Street, City, State 12345',
        //         'company_phone' => '+1 (555) 123-4567',
        //         'company_email' => 'info@'.strtolower(str_replace(' ', '', $company->name)).'.com',
        //         'show_qr_code' => false,
        //         'settings' => [
        //             'terms_and_conditions' => 'This credit note represents a credit to your account for refunds or corrections.',
        //         ],
        //     ]);
        }

        $this->command->info('Sample invoice templates created successfully!');
    }
}
