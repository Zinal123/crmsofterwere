<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invetry', function (Blueprint $table) {
            // Null means "use the global default" (Invetry::LOW_STOCK_THRESHOLD),
            // not "no threshold" - every item is always monitored.
            $table->unsignedInteger('low_stock_threshold')->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('invetry', function (Blueprint $table) {
            $table->dropColumn('low_stock_threshold');
        });
    }
};
