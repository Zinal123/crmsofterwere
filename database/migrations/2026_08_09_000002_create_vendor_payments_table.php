<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            // Null = a general/advance payment not tied to any specific bill.
            $table->foreignId('vendor_bill_id')->nullable()->constrained('vendor_bills')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->enum('payment_mode', ['cash', 'bank', 'upi', 'cheque']);
            $table->text('description')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payments');
    }
};
