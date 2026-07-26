<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_machine_id')->constrained('client_machines')->cascadeOnDelete();
            $table->foreignId('client_account_id')->constrained('client_accounts')->cascadeOnDelete();
            $table->foreignId('problem_type_id')->constrained('ticket_problem_types')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'assigned', 'in_progress', 'resolved'])->default('open');
            // Set once staff assigns the ticket and a Job is created for it - the
            // Job's own lifecycle (in_progress/completed) is the source of truth
            // for the work itself; this column just lets the inbox query/filter
            // tickets by status without joining to jobs on every list view.
            $table->foreignId('job_id')->nullable()->constrained('jobs')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
