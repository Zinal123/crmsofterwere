<?php
// tests/Feature/Ticketing/ProblemTypeEnrichmentTest.php
namespace Tests\Feature\Ticketing;

use App\Models\ChecklistTemplate;
use App\Models\TicketProblemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProblemTypeEnrichmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_problem_type_persists_all_new_metadata(): void
    {
        $tpl = ChecklistTemplate::create(['name' => 'Hydraulic PM', 'is_active' => true]);

        $type = TicketProblemType::create([
            'category' => 'hydraulic',
            'name' => 'Pump seal leak',
            'description' => 'Visible fluid under the pump housing.',
            'default_priority' => 'high',
            'estimated_resolution_hours' => 4,
            'checklist_template_id' => $tpl->id,
            'is_active' => true,
        ]);

        $fresh = $type->fresh();
        $this->assertSame('hydraulic', $fresh->category);
        $this->assertSame('high', $fresh->default_priority);
        $this->assertSame(4, $fresh->estimated_resolution_hours);
        $this->assertSame($tpl->id, $fresh->checklist_template_id);
    }

    public function test_deleting_the_linked_template_nulls_the_fk_not_the_type(): void
    {
        $tpl = ChecklistTemplate::create(['name' => 'X', 'is_active' => true]);
        $type = TicketProblemType::create([
            'category' => 'other', 'name' => 'Misc', 'checklist_template_id' => $tpl->id, 'is_active' => true,
        ]);

        $tpl->delete();

        $this->assertDatabaseHas('ticket_problem_types', ['id' => $type->id, 'checklist_template_id' => null]);
    }
}
