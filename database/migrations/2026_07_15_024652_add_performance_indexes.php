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
        Schema::table('cnsthinks', function (Blueprint $table) {
            $table->index('product_id');
        });
        Schema::table('fours', function (Blueprint $table) {
            $table->index('product_id');
        });
        Schema::table('power', function (Blueprint $table) {
            $table->index('product_id');
        });
        Schema::table('customer', function (Blueprint $table) {
            $table->index('invoice_id');
        });
        Schema::table('paidamount', function (Blueprint $table) {
            $table->index('invoice_id');
            $table->index('customer_id');
        });
        Schema::table('cuttingway', function (Blueprint $table) {
            $table->index('product_id');
        });
        Schema::table('softerwere1', function (Blueprint $table) {
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cnsthinks', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });
        Schema::table('fours', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });
        Schema::table('power', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });
        Schema::table('customer', function (Blueprint $table) {
            $table->dropIndex(['invoice_id']);
        });
        Schema::table('paidamount', function (Blueprint $table) {
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['customer_id']);
        });
        Schema::table('cuttingway', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });
        Schema::table('softerwere1', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });
    }
};
