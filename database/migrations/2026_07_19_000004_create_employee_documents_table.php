<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('document_type', ['aadhar', 'pan', 'driving_license', 'voter_id', 'other']);
            $table->string('document_number')->nullable();
            $table->string('path');
            // Plain integer() + explicit foreign(), not foreignId()->constrained(): this
            // app's live `users.id` column predates Laravel's id() convention and is a
            // signed `int`, not `unsigned bigint` - foreignId() would create a column
            // type MySQL refuses as FK-incompatible with the real users table (only
            // caught by testing against live MySQL; sqlite's weak typing let this pass
            // silently in the automated test suite).
            $table->integer('uploaded_by');
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
