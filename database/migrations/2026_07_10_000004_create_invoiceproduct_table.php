<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoiceproduct', function (Blueprint $table) {
            $table->id();
            $table->integer('invoice_id')->nullable();
            $table->integer('product_name')->nullable();
            $table->string('unit')->nullable();
            $table->string('hsn')->nullable();
            $table->string('rate')->nullable();
            $table->string('quantity')->nullable();
            $table->string('total')->nullable();
            $table->string('gst')->nullable();
            $table->string('gstamount')->nullable();
            $table->string('totalamount')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoiceproduct');
    }
};
