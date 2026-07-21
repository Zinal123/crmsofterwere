<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalize any pre-existing push_subscriptions rows that stored the raw FQCN
     * (App\Models\User) as subscribable_type before the global Relation::morphMap()
     * (added for the audit log feature) gave User the short alias 'user'. Without this,
     * rows created before the morph map was registered are silently excluded from the
     * webpush package's own subscribable_type = 'user' query.
     */
    public function up(): void
    {
        DB::table('push_subscriptions')
            ->where('subscribable_type', 'App\\Models\\User')
            ->update(['subscribable_type' => 'user']);
    }

    public function down(): void
    {
        DB::table('push_subscriptions')
            ->where('subscribable_type', 'user')
            ->update(['subscribable_type' => 'App\\Models\\User']);
    }
};
