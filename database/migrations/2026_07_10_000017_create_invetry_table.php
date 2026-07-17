<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invetry', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('vandername')->nullable();
            $table->string('rate')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invetry');
    }
};
