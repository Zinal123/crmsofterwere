<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL-only: fixes a live-data quirk on the pre-existing `rack`/`softerwere1`
        // tables. Tables created fresh (e.g. sqlite test DB) already get auto_increment
        // from their CREATE TABLE migration, so this raw MySQL DDL doesn't apply there
        // and would fail outright (sqlite has no MODIFY COLUMN syntax).
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `rack` MODIFY `id` INT AUTO_INCREMENT');
        DB::statement('ALTER TABLE `softerwere1` MODIFY `id` INT AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `rack` MODIFY `id` INT NOT NULL');
        DB::statement('ALTER TABLE `softerwere1` MODIFY `id` INT NOT NULL');
    }
};
