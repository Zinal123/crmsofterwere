<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank', function (Blueprint $table) {
            $table->id();
            $table->string('bankholdername')->nullable();
            $table->string('bankaccountnumber')->nullable();
            $table->string('bankifsccode')->nullable();
            $table->string('bankbranchname')->nullable();
            $table->string('bankname')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // Intentionally 'update_at', not 'updated_at' - matches the live schema
            // (Bank model has const UPDATED_AT = 'update_at' to compensate).
            $table->timestamp('update_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank');
    }
};
