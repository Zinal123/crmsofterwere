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
        DB::statement('ALTER TABLE `rack` MODIFY `id` INT AUTO_INCREMENT');
        DB::statement('ALTER TABLE `softerwere1` MODIFY `id` INT AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `rack` MODIFY `id` INT NOT NULL');
        DB::statement('ALTER TABLE `softerwere1` MODIFY `id` INT NOT NULL');
    }
};
