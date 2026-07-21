<?php

namespace Tests\Feature\Job;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NormalizePushSubscriptionMorphTypeMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_normalizes_legacy_fully_qualified_class_name_to_morph_alias(): void
    {
        $user = User::factory()->create();

        // Simulate a row created before the Relation::morphMap() was registered, i.e. before
        // this migration existed: bypass the model/morph map entirely and insert the raw
        // legacy FQCN directly, the way the webpush package would have stored it originally.
        DB::table('push_subscriptions')->insert([
            'subscribable_type' => 'App\\Models\\User',
            'subscribable_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/legacy-row',
            'public_key' => 'legacy-public-key',
            'auth_token' => 'legacy-auth-token',
            'content_encoding' => 'aesgcm',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id' => $user->id,
            'subscribable_type' => 'App\\Models\\User',
        ]);

        $migration = require database_path('migrations/2026_07_21_000002_normalize_push_subscription_morph_type.php');
        $migration->up();

        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id' => $user->id,
            'subscribable_type' => 'user',
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/legacy-row',
        ]);
        $this->assertDatabaseMissing('push_subscriptions', [
            'subscribable_type' => 'App\\Models\\User',
        ]);
    }

    public function test_migration_down_reverses_the_normalization(): void
    {
        $user = User::factory()->create();

        DB::table('push_subscriptions')->insert([
            'subscribable_type' => 'user',
            'subscribable_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/new-row',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_07_21_000002_normalize_push_subscription_morph_type.php');
        $migration->down();

        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id' => $user->id,
            'subscribable_type' => 'App\\Models\\User',
        ]);
    }
}
