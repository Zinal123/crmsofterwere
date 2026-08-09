<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->string('note')->nullable();
            // Plain integer() + explicit foreign(), not foreignId()->constrained(): this
            // app's live `users.id` column predates Laravel's id() convention and is a
            // signed `int`, not `unsigned bigint` - foreignId() would create a column
            // type MySQL refuses as FK-incompatible with the real users table (only
            // caught by testing against live MySQL; sqlite's weak typing let this pass
            // silently in the automated test suite).
            $table->unsignedBigInteger('paid_by');
            $table->timestamps();

            $table->foreign('paid_by')->references('id')->on('users');
            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
