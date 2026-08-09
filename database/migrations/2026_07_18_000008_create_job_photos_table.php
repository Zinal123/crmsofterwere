<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            // See 2026_07_18_000007_create_jobs_table.php: users.id is a legacy signed
            // int on this app's live DB, incompatible with foreignId()'s unsigned bigint.
            $table->unsignedBigInteger('uploaded_by');
            $table->string('path');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('location_captured')->default(false);
            $table->string('map_link')->nullable();
            $table->string('address')->nullable();
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_photos');
    }
};
