<?php
namespace Tests\Feature\Job;

use App\Models\ChecklistTemplate;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistTemplateAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_owner_can_create_a_template_and_see_it_on_the_index(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $this->actingAs($owner)
            ->post(route('admin.checklist-templates.store'), ['name' => 'Nozzle service'])
            ->assertRedirect();

        $this->actingAs($owner)
            ->get(route('admin.checklist-templates.index'))
            ->assertOk()
            ->assertSee('Nozzle service');
    }

    public function test_owner_can_add_an_item_to_a_template_with_a_position(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $template = ChecklistTemplate::create(['name' => 'Service', 'is_active' => true]);

        $this->actingAs($owner)
            ->post(route('admin.checklist-templates.items.store', $template->id), ['description' => 'Power off'])
            ->assertRedirect();

        $this->assertDatabaseHas('checklist_template_items', [
            'checklist_template_id' => $template->id,
            'description' => 'Power off',
            'position' => 1,
        ]);
    }

    public function test_a_user_without_the_permission_is_forbidden(): void
    {
        $user = User::factory()->create();
        // UserFactory attaches Owner by default (documented gotcha) - isolate Worker.
        $user->syncRoles(['Worker']);

        $this->actingAs($user)
            ->get(route('admin.checklist-templates.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.checklist-templates.store'), ['name' => 'Nope'])
            ->assertForbidden();
    }
}
