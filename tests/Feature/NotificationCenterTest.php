<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\JobDecisionNotification;
use App\Notifications\JobOverdueNotification;
use App\Notifications\LowStockNotification;
use App\Notifications\TicketStatusChangedNotification;
use Database\Seeders\ChartOfAccountsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ChartOfAccountsSeeder::class);
    }

    private function pushNotification(User $user, string $type, array $data): void
    {
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => $type,
            // The app maps User to the 'user' morph alias, which is what
            // $user->notifications() queries on.
            'notifiable_type' => 'user',
            'notifiable_id' => $user->id,
            'data' => json_encode($data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_notification_center_renders_every_notification_type(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $this->pushNotification($owner, JobOverdueNotification::class, [
            'job_id' => 5, 'job_title' => 'Nozzle replacement', 'due_date' => '2026-08-01',
        ]);
        $this->pushNotification($owner, LowStockNotification::class, [
            'invetry_id' => 3, 'product_name' => 'Fiber Laser Nozzle', 'quantity' => 2,
        ]);
        $this->pushNotification($owner, JobDecisionNotification::class, [
            'job_id' => 6, 'job_title' => 'Lens cleaning', 'decision' => 'approved', 'reason' => null,
        ]);
        $this->pushNotification($owner, TicketStatusChangedNotification::class, [
            'ticket_id' => 9, 'status' => 'in_progress',
        ]);

        $response = $this->actingAs($owner)->get(route('root'));

        $response->assertOk();
        // Overdue job — its own text, not the generic "assigned" text.
        $response->assertSee('Nozzle replacement');
        $response->assertSee('Job is overdue (due 01 Aug 2026).');
        // Low stock renders with product name + quantity (no job link crash).
        $response->assertSee('Fiber Laser Nozzle');
        $response->assertSee('Low stock — only 2 left.');
        // Job decision still works.
        $response->assertSee('Job request approved.');
        // Ticket status renders.
        $response->assertSee('Support ticket #9');
        $response->assertSee('Status changed to In progress.');
    }

    public function test_unread_badge_counts_all_notification_types(): void
    {
        $owner = $this->owner();
        $this->pushNotification($owner, LowStockNotification::class, ['invetry_id' => 1, 'product_name' => 'X', 'quantity' => 0]);
        $this->pushNotification($owner, JobOverdueNotification::class, ['job_id' => 1, 'job_title' => 'Y', 'due_date' => null]);

        $this->assertEquals(2, $owner->unreadNotifications()->count());
    }

    public function test_a_single_notification_can_be_marked_read(): void
    {
        $owner = $this->owner();
        $this->pushNotification($owner, LowStockNotification::class, ['invetry_id' => 1, 'product_name' => 'X', 'quantity' => 0]);
        $id = $owner->notifications()->first()->id;

        $this->actingAs($owner)->post(route('notifications.read', $id))->assertNoContent();

        $this->assertEquals(0, $owner->fresh()->unreadNotifications()->count());
    }

    public function test_mark_all_read_clears_the_unread_count(): void
    {
        $owner = $this->owner();
        $this->pushNotification($owner, LowStockNotification::class, ['invetry_id' => 1, 'product_name' => 'X', 'quantity' => 0]);
        $this->pushNotification($owner, JobOverdueNotification::class, ['job_id' => 1, 'job_title' => 'Y', 'due_date' => null]);

        $this->actingAs($owner)->post(route('notifications.read-all'))->assertRedirect();

        $this->assertEquals(0, $owner->fresh()->unreadNotifications()->count());
    }

    public function test_view_all_notifications_link_appears_in_the_dropdown(): void
    {
        $owner = $this->owner();

        $response = $this->actingAs($owner)->get(route('root'));

        $response->assertOk();
        $response->assertSee(route('notifications.index'), false);
    }

    public function test_notifications_index_shows_more_than_the_dropdowns_10_item_limit(): void
    {
        // The topbar dropdown only ever loads the latest 10 - this page is
        // specifically for seeing everything beyond that.
        $owner = $this->owner();
        for ($i = 1; $i <= 15; $i++) {
            $this->pushNotification($owner, LowStockNotification::class, [
                'invetry_id' => $i, 'product_name' => "Part $i", 'quantity' => 0,
            ]);
        }

        $response = $this->actingAs($owner)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Part 1');
        $response->assertSee('Part 11');
    }

    public function test_notifications_index_pagination_is_bootstrap_styled(): void
    {
        // Laravel's default pagination view is Tailwind-styled - this app
        // has no Tailwind CSS loaded (Bootstrap 5 throughout), so it would
        // render as unstyled plain links without the AppServiceProvider fix.
        $owner = $this->owner();
        for ($i = 1; $i <= 25; $i++) {
            $this->pushNotification($owner, LowStockNotification::class, ['invetry_id' => $i, 'product_name' => "Part $i", 'quantity' => 0]);
        }

        $response = $this->actingAs($owner)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('page-link', false);
        $response->assertDontSee('sm:hidden', false);
    }

    public function test_notifications_index_only_shows_the_current_users_own_notifications(): void
    {
        $owner = $this->owner();
        $other = $this->owner();
        $this->pushNotification($owner, LowStockNotification::class, ['invetry_id' => 1, 'product_name' => 'My Part', 'quantity' => 0]);
        $this->pushNotification($other, LowStockNotification::class, ['invetry_id' => 2, 'product_name' => 'Someone Elses Part', 'quantity' => 0]);

        $response = $this->actingAs($owner)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('My Part');
        $response->assertDontSee('Someone Elses Part');
    }

    public function test_a_user_cannot_mark_another_users_notification_read(): void
    {
        $owner = $this->owner();
        $other = $this->owner();
        $this->pushNotification($other, LowStockNotification::class, ['invetry_id' => 1, 'product_name' => 'X', 'quantity' => 0]);
        $id = $other->notifications()->first()->id;

        // Acting as $owner, hitting $other's notification id does nothing.
        $this->actingAs($owner)->post(route('notifications.read', $id))->assertNoContent();

        $this->assertEquals(1, $other->fresh()->unreadNotifications()->count());
    }

    private function owner(): User
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        return $owner;
    }
}
