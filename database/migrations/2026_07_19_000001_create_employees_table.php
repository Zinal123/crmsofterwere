<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->date('joining_date');
            $table->enum('pay_type', ['monthly', 'daily']);
            $table->decimal('pay_rate', 10, 2);
            $table->decimal('overtime_rate_per_hour', 10, 2)->default(0);
            $table->string('bank_account_holder_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc')->nullable();
            $table->string('bank_name')->nullable();
            // Plain integer() + explicit foreign(), not foreignId()->constrained(): this
            // app's live `users.id` column predates Laravel's id() convention and is a
            // signed `int`, not `unsigned bigint` - foreignId() would create a column
            // type MySQL refuses as FK-incompatible with the real users table (only
            // caught by testing against live MySQL; sqlite's weak typing let this pass
            // silently in the automated test suite).
            $table->integer('user_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
