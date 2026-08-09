<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * job_photos and ticket_photos were two independently-evolving photo
     * tables for the same concept (a captured photo attached to either a
     * Job or a Ticket) - job_photos grew a full proof-of-work integrity
     * layer (hash, GPS, geofencing, stage) that ticket_photos never got.
     * This merges ticket_photos into job_photos (job_id/uploaded_by/
     * captured_at become nullable, ticket_id is added) so there is one
     * canonical photo table going forward.
     *
     * doctrine/dbal isn't installed, so Schema::table(...)->change() isn't
     * available (same constraint as 2026_07_22_000001). Rather than hand-write
     * driver-specific ALTER statements, this rebuilds the table via Blueprint
     * (portable across mysql/sqlite) and copies the data across - the
     * standard approach when a table's column *constraints* need to change,
     * not just new columns added.
     */
    public function up(): void
    {
        Schema::create('job_photos_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->nullable()->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->cascadeOnDelete();
            // Nullable now - a client-submitted ticket photo has no staff uploader.
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('path');
            $table->string('content_hash', 64)->nullable();
            $table->enum('stage', ['before', 'after', 'general'])->default('general');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('location_captured')->default(false);
            $table->boolean('location_flagged')->default(false);
            $table->decimal('distance_from_machine_meters', 10, 2)->nullable();
            $table->string('map_link')->nullable();
            $table->string('address')->nullable();
            // Nullable now - only meaningful for the GPS-mandatory Job proof-of-work flow.
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users')->cascadeOnDelete();
        });

        foreach (DB::table('job_photos')->orderBy('id')->get() as $row) {
            DB::table('job_photos_new')->insert((array) $row);
        }

        foreach (DB::table('ticket_photos')->orderBy('id')->get() as $row) {
            DB::table('job_photos_new')->insert([
                'ticket_id' => $row->ticket_id,
                'path' => $row->path,
                'stage' => 'general',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::drop('job_photos');
        Schema::rename('job_photos_new', 'job_photos');
        Schema::drop('ticket_photos');
    }

    public function down(): void
    {
        Schema::create('ticket_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });

        foreach (DB::table('job_photos')->whereNotNull('ticket_id')->orderBy('id')->get() as $row) {
            DB::table('ticket_photos')->insert([
                'ticket_id' => $row->ticket_id,
                'path' => $row->path,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::create('job_photos_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->unsignedBigInteger('uploaded_by');
            $table->string('path');
            $table->string('content_hash', 64)->nullable();
            $table->enum('stage', ['before', 'after', 'general'])->default('general');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('location_captured')->default(false);
            $table->boolean('location_flagged')->default(false);
            $table->decimal('distance_from_machine_meters', 10, 2)->nullable();
            $table->string('map_link')->nullable();
            $table->string('address')->nullable();
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users')->cascadeOnDelete();
        });

        foreach (DB::table('job_photos')->whereNull('ticket_id')->orderBy('id')->get() as $row) {
            DB::table('job_photos_old')->insert([
                'job_id' => $row->job_id,
                'uploaded_by' => $row->uploaded_by,
                'path' => $row->path,
                'content_hash' => $row->content_hash,
                'stage' => $row->stage,
                'latitude' => $row->latitude,
                'longitude' => $row->longitude,
                'location_captured' => $row->location_captured,
                'location_flagged' => $row->location_flagged,
                'distance_from_machine_meters' => $row->distance_from_machine_meters,
                'map_link' => $row->map_link,
                'address' => $row->address,
                'captured_at' => $row->captured_at,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::drop('job_photos');
        Schema::rename('job_photos_old', 'job_photos');
    }
};
