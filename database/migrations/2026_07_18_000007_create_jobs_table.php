<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete();
            $table->string('site_name')->nullable();
            // Plain integer() + explicit foreign(), not foreignId()->constrained(): this
            // app's live `users.id` column predates Laravel's id() convention and is a
            // signed `int`, not `unsigned bigint` - foreignId() would create a column
            // type MySQL refuses as FK-incompatible with the real users table (only
            // caught by testing against live MySQL; sqlite's weak typing let this pass
            // silently in the automated test suite).
            $table->integer('created_by');
            $table->integer('assigned_to')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending_approval', 'assigned', 'in_progress', 'on_hold', 'completed', 'rejected']);
            $table->integer('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('on_hold_reason')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('decided_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
