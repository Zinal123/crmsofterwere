<?php

namespace Tests\Feature\Ticketing;

use App\Models\TicketProblemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriorityBadgeConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_low_and_medium_priority_render_visually_distinct_badges(): void
    {
        $owner = \App\Models\User::factory()->create();
        $low = TicketProblemType::create(['category' => 'other', 'name' => 'Low one', 'default_priority' => 'low', 'is_active' => true]);
        $medium = TicketProblemType::create(['category' => 'other', 'name' => 'Medium one', 'default_priority' => 'medium', 'is_active' => true]);

        $res = $this->actingAs($owner)->get(route('admin.ticket-problem-types.index'));

        $res->assertOk();
        // Low -> bg-light-subtle, Medium -> bg-secondary-subtle: must not be the same class.
        $res->assertSee('bg-light-subtle');
        $res->assertSee('bg-secondary-subtle');
    }
}
