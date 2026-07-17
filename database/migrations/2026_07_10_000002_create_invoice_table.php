<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->nullable();
            $table->date('date')->nullable();
            $table->string('bankname')->nullable();
            $table->string('accountholder')->nullable();
            $table->string('bankaccountnumber')->nullable();
            $table->string('bankifsccode')->nullable();
            $table->string('bankbranchname')->nullable();
            $table->text('notes')->nullable();
            $table->string('totalamountbeforetax')->nullable();
            $table->string('amount')->nullable();
            $table->string('amountwithtax')->nullable();
            $table->string('paycondition')->nullable();
            $table->date('duedate')->nullable();
            $table->string('placesupply');
            $table->string('paidamount')->nullable();
            $table->string('remaining_amount')->nullable();
            $table->string('challanno')->nullable();
            $table->string('ewaybillno')->nullable();
            $table->date('ewaybilldate')->nullable();
            $table->string('despatchthrough')->nullable();
            $table->string('TransportVehicleNo')->nullable();
            $table->string('pono')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
