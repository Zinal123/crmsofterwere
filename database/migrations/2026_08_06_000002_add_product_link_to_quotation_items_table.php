<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            // Optional: most pricing rows are freeform (installation, transport,
            // training, discounts) and have nothing to link. When a row does
            // represent a real tracked part, linking it lets staff see live
            // stock availability against it, same as the spare-parts BOM engine.
            $table->foreignId('product_id')->nullable()->after('quotation_id')->constrained('product')->nullOnDelete();
            $table->unsignedInteger('quantity')->nullable()->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn('quantity');
        });
    }
};
