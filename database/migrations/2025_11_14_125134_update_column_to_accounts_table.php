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
        Schema::table('accounts', function (Blueprint $table) {
            $table->renameColumn('bank_name', 'bank_id');
            $table->unsignedBigInteger('bank_id')->change();
            $table->foreign('bank_id')->references('id')->on('banks')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->string('bank_id')->change();
            $table->renameColumn('bank_id', 'bank_name');
        });
    }
};
