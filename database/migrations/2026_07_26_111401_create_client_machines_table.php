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
        Schema::create('client_machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_account_id')->constrained('client_accounts')->cascadeOnDelete();
            // product/invoice.id are plain signed `int` on this app's live schema
            // (predates Laravel's bigint id() convention) - see jobs table
            // migration for the same discipline. foreignId() would create a
            // column type MySQL refuses as FK-incompatible with the real tables.
            $table->integer('product_id');
            $table->integer('invoice_id')->nullable();
            $table->string('serial_number');
            $table->date('installed_at')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('product')->restrictOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoice')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_machines');
    }
};
