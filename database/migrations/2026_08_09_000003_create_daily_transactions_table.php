<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['payment', 'receipt']);
            $table->foreignId('expense_category_id')->constrained('expense_categories');
            // Simple string+id, not a true Eloquent morphTo: this app's other
            // multi-actor fields (Job's created_by/assigned_to/decided_by)
            // follow the same plain-column convention rather than polymorphic
            // relations, and party_type only ever needs to be one of the
            // three fixed strings ExpenseCategory.party_model already uses.
            $table->string('party_type')->nullable();
            $table->unsignedBigInteger('party_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->enum('payment_mode', ['cash', 'bank', 'upi', 'cheque']);
            $table->text('description')->nullable();
            $table->string('receipt_photo')->nullable();
            // The anti-double-counting fields: when a category requires a
            // party, the real payment (SalaryPayment/VendorPayment) is
            // created through its own owning service, and this row only
            // links to it - see DailyTransactionService.
            $table->string('linked_type')->nullable();
            $table->unsignedBigInteger('linked_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['type', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_transactions');
    }
};
