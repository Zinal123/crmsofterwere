<?php
// database/migrations/2026_08_16_000000_enrich_ticket_problem_types.php
// NOTE: dated after 2026_08_15 checklist_templates so the FK's parent table
// exists at migration time (required on MySQL; makes nullOnDelete work on SQLite).
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // category: enum -> string (so new categories need no enum migration).
        // SQLite can't ALTER an enum; recreate via a temporary string column.
        Schema::table('ticket_problem_types', function (Blueprint $table) {
            $table->string('category_tmp')->nullable()->after('id');
        });
        DB::table('ticket_problem_types')->update(['category_tmp' => DB::raw('category')]);
        Schema::table('ticket_problem_types', function (Blueprint $table) {
            $table->dropColumn('category');
        });
        Schema::table('ticket_problem_types', function (Blueprint $table) {
            $table->renameColumn('category_tmp', 'category');
        });

        Schema::table('ticket_problem_types', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('default_priority')->default('medium')->after('description');
            $table->unsignedInteger('estimated_resolution_hours')->nullable()->after('default_priority');
        });

        // checklist_template_id FK.
        // SQLite's ALTER-based schema grammar silently drops the REFERENCES clause when
        // a column is added to an existing table, so the constraint is never created and
        // nullOnDelete can't fire. Add it with raw SQL there (SQLite supports inline
        // REFERENCES on ADD COLUMN for a nullable column). Other drivers use constrained().
        if (DB::getDriverName() === 'sqlite') {
            DB::statement(
                'ALTER TABLE ticket_problem_types ADD COLUMN checklist_template_id integer '
                . 'REFERENCES checklist_templates(id) ON DELETE SET NULL'
            );
        } else {
            Schema::table('ticket_problem_types', function (Blueprint $table) {
                $table->foreignId('checklist_template_id')->nullable()->after('estimated_resolution_hours')
                    ->constrained('checklist_templates')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('ticket_problem_types', function (Blueprint $table) {
                $table->dropColumn(['checklist_template_id', 'description', 'default_priority', 'estimated_resolution_hours']);
            });
        } else {
            Schema::table('ticket_problem_types', function (Blueprint $table) {
                $table->dropConstrainedForeignId('checklist_template_id');
                $table->dropColumn(['description', 'default_priority', 'estimated_resolution_hours']);
            });
        }
        // category stays a string on rollback (acceptable — no data loss).
    }
};
