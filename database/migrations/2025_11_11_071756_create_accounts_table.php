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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('account_number')->nullable();
            $table->enum('account_type', ['cash', 'bank'])->default('bank');
            $table->foreignId('bank_id')
                ->nullable()
                ->constrained('banks')
                ->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
