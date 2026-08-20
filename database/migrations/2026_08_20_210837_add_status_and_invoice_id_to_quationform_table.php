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
        Schema::table('quationform', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('id');
            // Plain integer, not foreignId - invoice.id is a plain signed
            // int on this app's real schema (see create_invoice_table's own
            // comment), not Laravel's bigint default.
            $table->integer('invoice_id')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quationform', function (Blueprint $table) {
            $table->dropColumn(['status', 'invoice_id']);
        });
    }
};
