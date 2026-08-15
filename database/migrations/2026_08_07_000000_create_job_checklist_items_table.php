<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A prior deploy attempt got as far as creating this table (with the
        // since-fixed wrong column type for completed_by) before the FK
        // constraint step failed - MySQL doesn't roll the CREATE back when a
        // later implicit ALTER for the constraint errors, so the table was
        // left sitting on production without this migration ever being
        // recorded as run. This feature was never live (the migration never
        // completed), so there's no real data at risk - drop and recreate
        // cleanly rather than trying to ALTER the orphaned table's columns.
        Schema::dropIfExists('job_checklist_items');

        Schema::create('job_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->string('description');
            $table->boolean('is_completed')->default(false);
            // Plain integer, not foreignId()->constrained(): users.id predates
            // Laravel's bigint id() convention (see jobs table migration for
            // the same discipline).
            $table->integer('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('completed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_checklist_items');
    }
};
