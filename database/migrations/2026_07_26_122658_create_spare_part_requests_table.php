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
        Schema::create('spare_part_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_machine_id')->constrained('client_machines')->cascadeOnDelete();
            $table->foreignId('client_account_id')->constrained('client_accounts')->cascadeOnDelete();
            // product.id is a plain signed `int` on this app's live schema, same
            // discipline as client_machines.product_id in an earlier migration -
            // foreignId() would create a bigint unsigned column MySQL refuses as
            // FK-incompatible with the real product table.
            $table->integer('product_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'approved', 'fulfilled', 'rejected'])->default('pending');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('product')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_part_requests');
    }
};
