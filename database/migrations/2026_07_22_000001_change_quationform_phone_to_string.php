<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // sqlite (this app's test DB) uses type affinity, not strict typing -
        // its "integer" column already silently accepts a 10-digit phone
        // number as a 64-bit value with no overflow, so there is nothing to
        // migrate there. This bug is MySQL-only (32-bit int max ~2.1
        // billion, any real mobile number overflows it) - same
        // sqlite-vs-MySQL gap pattern documented elsewhere in this project.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // doctrine/dbal isn't installed, so Schema::table(...)->change() isn't
        // available here - a raw ALTER is the standard Laravel fallback.
        // quationform.phone was the only phone-like column in this app not
        // using `string` (customer.phone, employees.phone, etc. all do).
        DB::statement('ALTER TABLE quationform MODIFY phone VARCHAR(20) NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE quationform MODIFY phone INT NULL');
    }
};
