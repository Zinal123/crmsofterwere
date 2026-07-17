<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addlaser', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable()->index();
            $table->string('company')->nullable();
            $table->string('modal')->nullable();
            $table->string('logo')->nullable();
            $table->string('image')->nullable();
            $table->text('decription')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // Intentionally 'update_at', not 'updated_at' - matches the live schema
            // (Lasercutting model has const UPDATED_AT = 'update_at' to compensate).
            $table->timestamp('update_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addlaser');
    }
};
