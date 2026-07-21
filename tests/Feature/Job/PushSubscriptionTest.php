<?php

namespace Tests\Feature\Job;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_a_push_subscription(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('push-subscriptions.store'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
            'publicKey' => 'test-public-key',
            'authToken' => 'test-auth-token',
            'contentEncoding' => 'aesgcm',
        ]);

        $response->assertOk();
        // 'user' not User::class: app/Providers/AppServiceProvider.php registers a global
        // Relation::morphMap() (added for the audit log feature) that gives User the short
        // alias 'user' for all polymorphic relations app-wide, including this pre-existing
        // webpush subscribable_type column.
        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id' => $user->id,
            'subscribable_type' => 'user',
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        ]);
    }
}
