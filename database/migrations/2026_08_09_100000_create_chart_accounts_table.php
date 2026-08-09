<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The Chart of Accounts is the fixed reference structure the
        // accounting reports (Trial Balance, P&L) organise around. It is
        // reference data only - no existing table gains a foreign key to it,
        // and nothing about how transactions are recorded changes. The
        // reports derive account balances from the existing DailyTransaction/
        // Invoice/VendorBill/SalaryPayment rows at read time (cash basis),
        // rather than posting journal entries.
        Schema::create('chart_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'income', 'expense']);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_accounts');
    }
};
