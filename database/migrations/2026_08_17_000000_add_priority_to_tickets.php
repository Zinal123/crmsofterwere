<?php
// database/migrations/2026_08_17_000000_add_priority_to_tickets.php
// Dated after 2026_08_16_000000_enrich_ticket_problem_types so this feature's
// migrations stay chronologically ordered as a set.
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('priority')->default('medium')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
