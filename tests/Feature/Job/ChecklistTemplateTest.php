<?php
namespace Tests\Feature\Job;

use App\Models\ChecklistTemplate;
use App\Models\ChecklistTemplateItem;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_a_template_has_ordered_items(): void
    {
        $t = ChecklistTemplate::create(['name' => 'Nozzle service', 'is_active' => true]);
        ChecklistTemplateItem::create(['checklist_template_id' => $t->id, 'description' => 'Power off', 'position' => 2]);
        ChecklistTemplateItem::create(['checklist_template_id' => $t->id, 'description' => 'Lock out', 'position' => 1]);

        $descriptions = $t->fresh()->items->pluck('description')->all();
        $this->assertSame(['Lock out', 'Power off'], $descriptions);
    }
}
