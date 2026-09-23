<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tally_connections', function (Blueprint $table) {
            $table->id();
            $table->string('host');
            $table->unsignedInteger('port')->default(9000);
            $table->string('company_name');
            $table->string('username');
            $table->text('password');
            $table->enum('status', ['disconnected', 'connected', 'failed'])->default('disconnected');
            $table->timestamp('last_checked_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tally_connections');
    }
};
