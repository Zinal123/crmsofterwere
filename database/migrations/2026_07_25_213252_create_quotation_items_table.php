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
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            // quationform.id is a plain signed `int` (hand-translated from
            // the live schema before migrations existed, not Laravel's usual
            // bigint unsigned `id()`), so the FK column here has to match
            // both the width AND signedness exactly - MySQL rejects a
            // foreign key between mismatched integer types.
            $table->integer('quotation_id');
            $table->foreign('quotation_id')->references('id')->on('quationform')->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->string('amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
