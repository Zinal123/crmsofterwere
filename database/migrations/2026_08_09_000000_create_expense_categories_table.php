<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['payment', 'receipt']);
            // Null = no party required (e.g. Electricity, Diesel). When set,
            // the Daily Transaction form requires picking that kind of party
            // and DailyTransactionService routes the entry through the real
            // owning module (payroll for 'employee', the new vendor payable
            // ledger for 'vendor') instead of writing a disconnected row.
            $table->enum('party_model', ['employee', 'vendor', 'client_account'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
