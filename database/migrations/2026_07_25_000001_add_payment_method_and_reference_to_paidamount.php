<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paidamount', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('paidAmount');
            $table->string('reference_number')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('paidamount', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'reference_number']);
        });
    }
};
