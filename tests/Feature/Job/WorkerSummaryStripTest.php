<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerSummaryStripTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_worker_landing_shows_correct_summary_counts(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        // 2 assigned (todo)
        Job::factory()->create(['status' => 'assigned', 'assigned_to' => $worker->id]);
        Job::factory()->create(['status' => 'assigned', 'assigned_to' => $worker->id]);

        // 1 in_progress
        Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);

        // 1 overdue (flagged, not done)
        Job::factory()->create([
            'status' => 'in_progress',
            'assigned_to' => $worker->id,
            'overdue_flagged_at' => now(),
        ]);

        // 1 completed today
        Job::factory()->create([
            'status' => 'completed',
            'assigned_to' => $worker->id,
            'completed_at' => now(),
        ]);

        $res = $this->actingAs($worker)->get(route('jobs.index'));

        $res->assertOk();
        $res->assertSee('In Progress');
        $res->assertSee('Overdue');
        $res->assertSeeText('Completed Today');

        $content = $res->getContent();
        $this->assertStringContainsString('id="worker-summary"', $content);

        preg_match('/<div id="worker-summary".*?<\/div>\s*<\/div>\s*<\/div>/s', $content, $m);
        $this->assertNotEmpty($m, 'worker-summary strip not found');

        $start = strpos($content, 'id="worker-summary"');
        $strip = substr($content, $start, 2000);

        // 2 assigned = todo, 2 in_progress (one of which is also overdue-flagged), 1 overdue, 1 completed_today
        $this->assertMatchesRegularExpression('/>\s*2\s*<.*?To Do/s', $strip);
        $this->assertMatchesRegularExpression('/>\s*2\s*<.*?In Progress/s', $strip);
        $this->assertMatchesRegularExpression('/>\s*1\s*<.*?Overdue/s', $strip);
        $this->assertMatchesRegularExpression('/>\s*1\s*<.*?Completed Today/s', $strip);
    }
}
