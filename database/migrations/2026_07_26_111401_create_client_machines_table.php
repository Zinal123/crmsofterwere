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
            // product/invoice.id are plain SIGNED `int` on this app's live
            // schema (predates Laravel's bigint id() convention - see
            // create_product_table/create_invoice_table). unsignedBigInteger()
            // (and foreignId()) create a column type MySQL refuses as
            // FK-incompatible with the real tables - confirmed live via the
            // identical errno 150/3780 on the quotation_items migration.
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
