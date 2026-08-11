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

    private function owner(): User
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        return $owner;
    }
}
