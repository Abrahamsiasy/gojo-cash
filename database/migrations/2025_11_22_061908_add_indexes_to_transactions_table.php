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
        Schema::table('transactions', function (Blueprint $table) {
            // For date range filtering (Income/Expense Chart)
            $table->index(['company_id', 'date']);

            // For filtering by type (Income/Expense Chart, Transactions by Type)
            // Also helps when filtering by type AND date
            $table->index(['company_id', 'type', 'date']);

            // For filtering by category (Transactions by Category)
            $table->index(['company_id', 'type', 'category_id']);

            // For filtering by client (if client_id is used in filters)
            $table->index(['company_id', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'date']);
            $table->dropIndex(['company_id', 'type', 'date']);
            $table->dropIndex(['company_id', 'type', 'category_id']);
            $table->dropIndex(['company_id', 'client_id']);
        });
    }
};
