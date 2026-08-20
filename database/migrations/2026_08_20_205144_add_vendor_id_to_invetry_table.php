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
        Schema::table('invetry', function (Blueprint $table) {
            // Nullable and separate from the existing free-text vandername
            // column - a real link to the Vendor system for a tracked
            // supplier, while untracked/one-off suppliers can still be
            // recorded as plain text as before.
            $table->foreignId('vendor_id')->nullable()->after('vandername')->constrained('vendors')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invetry', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
        });
    }
};
