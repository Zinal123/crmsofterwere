<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('power', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable();
            $table->string('company')->nullable();
            $table->string('modal')->nullable();
            $table->string('image')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('power');
    }
};
