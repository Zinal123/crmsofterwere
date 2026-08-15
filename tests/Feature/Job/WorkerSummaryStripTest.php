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

    /**
     * Extracts the worker-summary strip's markup (from id="worker-summary" up
     * to the "My Jobs" <h5> that immediately follows it per
     * resources/views/worker/jobs/index.blade.php) so count assertions can't
     * accidentally match unrelated numbers in the job cards below.
     */
    private function extractWorkerSummaryStrip(string $content): string
    {
        preg_match('/id="worker-summary".*?(?=<h5)/s', $content, $m);
        $this->assertNotEmpty($m, 'worker-summary strip not found in response');

        return $m[0];
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

        $strip = $this->extractWorkerSummaryStrip($res->getContent());

        // 2 assigned = todo, 2 in_progress (one of which is also overdue-flagged), 1 overdue, 1 completed_today
        $this->assertMatchesRegularExpression('/>\s*2\s*<.*?To Do/s', $strip);
        $this->assertMatchesRegularExpression('/>\s*2\s*<.*?In Progress/s', $strip);
        $this->assertMatchesRegularExpression('/>\s*1\s*<.*?Overdue/s', $strip);
        $this->assertMatchesRegularExpression('/>\s*1\s*<.*?Completed Today/s', $strip);
    }

    public function test_a_job_updated_today_but_completed_yesterday_does_not_count_as_completed_today(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        Job::factory()->create([
            'assigned_to' => $worker->id,
            'status' => 'completed',
            'completed_at' => now()->subDay(),
        ]);

        $res = $this->actingAs($worker)->get(route('jobs.index'));

        $res->assertOk();

        $strip = $this->extractWorkerSummaryStrip($res->getContent());

        $this->assertMatchesRegularExpression('/>\s*0\s*<.*?Completed Today/s', $strip);
    }

    public function test_a_rejected_but_still_flagged_job_does_not_count_as_overdue(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        Job::factory()->create([
            'assigned_to' => $worker->id,
            'status' => 'rejected',
            'overdue_flagged_at' => now()->subHour(),
        ]);

        $res = $this->actingAs($worker)->get(route('jobs.index'));

        $res->assertOk();

        $strip = $this->extractWorkerSummaryStrip($res->getContent());

        $this->assertMatchesRegularExpression('/>\s*0\s*<.*?Overdue/s', $strip);
    }
}
