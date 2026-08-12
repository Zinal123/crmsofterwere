<?php
namespace Tests\Feature\Job;

use App\Models\ChecklistTemplate;
use App\Models\ChecklistTemplateItem;
use App\Models\Job;
use App\Models\User;
use App\Services\Job\ChecklistTemplateService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistTemplateApplyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_applying_a_template_adds_its_items_to_the_job_checklist(): void
    {
        $job = Job::factory()->create();
        $t = ChecklistTemplate::create(['name' => 'Service', 'is_active' => true]);
        ChecklistTemplateItem::create(['checklist_template_id' => $t->id, 'description' => 'Step A', 'position' => 1]);
        ChecklistTemplateItem::create(['checklist_template_id' => $t->id, 'description' => 'Step B', 'position' => 2]);

        $count = app(ChecklistTemplateService::class)->applyToJob($t, $job);

        $this->assertSame(2, $count);
        $this->assertSame(['Step A', 'Step B'], $job->fresh()->checklistItems->pluck('description')->all());
    }

    public function test_owner_can_apply_a_template_to_a_job_over_http(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['created_by' => $owner->id]);
        $t = ChecklistTemplate::create(['name' => 'Svc', 'is_active' => true]);
        ChecklistTemplateItem::create(['checklist_template_id' => $t->id, 'description' => 'Do X', 'position' => 1]);

        $this->actingAs($owner)->post(route('jobs.apply-template', $job->id), ['checklist_template_id' => $t->id])->assertRedirect();
        $this->assertDatabaseHas('job_checklist_items', ['job_id' => $job->id, 'description' => 'Do X']);
    }
}
