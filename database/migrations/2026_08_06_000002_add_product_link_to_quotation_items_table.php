<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // product.id is a plain SIGNED `int` on this app's live schema
        // (predates Laravel's bigint id() convention - see client_machines/
        // spare_part_requests migrations for the same discipline). The
        // original version of this migration used foreignId(), which creates
        // a `bigint unsigned` column - MySQL refuses to constrain that
        // against a mismatched-type primary key (errno 150: "foreign key
        // constraint is incorrectly formed"). Confirmed live on production:
        // MySQL auto-commits each ALTER independently, so a prior deploy
        // attempt left product_id sitting on quotation_items as the wrong
        // type with no constraint. Drop-and-recreate with the matching type
        // if that partial state exists; add fresh (also with the matching
        // type) otherwise - both paths converge on the same correct schema.
        if (Schema::hasColumn('quotation_items', 'product_id')) {
            Schema::table('quotation_items', function (Blueprint $table) {
                $table->dropColumn('product_id');
            });
        }

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->integer('product_id')->nullable()->after('quotation_id');

            if (! Schema::hasColumn('quotation_items', 'quantity')) {
                $table->unsignedInteger('quantity')->nullable()->after('product_id');
            }
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('product')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->dropColumn('quantity');
        });
    }
};
