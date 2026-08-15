<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product', function (Blueprint $table) {
            // Plain signed auto-increment int, matching this app's actual
            // live schema (predates Laravel's bigint id() convention) - every
            // other migration referencing product_id (gear, motor, power,
            // rack, invetry, cuttingway, etc.) already uses plain integer(),
            // and $table->id() here would silently diverge from that on a
            // fresh install (confirmed live: production's product.id is
            // `int(11)` signed, not bigint unsigned).
            $table->integer('id', true);
            $table->string('name')->nullable();
            $table->string('rate')->nullable();
            $table->string('unit')->nullable();
            $table->string('make')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
