<?php

namespace Tests\Feature\Job;

use App\Models\Invetry;
use App\Models\Job;
use App\Models\Product;
use App\Models\User;
use App\Services\Job\JobMaterialService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobMaterialManageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeProductWithStock(string $name, int $onHand): Product
    {
        $p = Product::create(['name' => $name, 'rate' => '1', 'unit' => 'pcs', 'make' => 'X']);
        Invetry::create(['product_id' => $p->id, 'quantity' => $onHand, 'vandername' => 'V', 'rate' => '1']);

        return $p;
    }

    private function owner(): User
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        return $owner;
    }

    public function test_availability_for_flags_shortages(): void
    {
        $job = Job::factory()->create(['status' => 'assigned']);
        $p1 = $this->makeProductWithStock('Nozzle', 5);
        $p2 = $this->makeProductWithStock('Lens', 1);

        $svc = app(JobMaterialService::class);
        $svc->add($job, $p1->id, 2);
        $svc->add($job, $p2->id, 3);

        $result = $svc->availabilityFor($job);

        $this->assertFalse($result['all_available']);
        $this->assertCount(2, $result['lines']);
    }

    public function test_owner_can_add_a_material_via_http(): void
    {
        $job = Job::factory()->create(['status' => 'assigned']);
        $p = $this->makeProductWithStock('Belt', 5);

        $response = $this->actingAs($this->owner())
            ->post(route('jobs.materials.store', $job->id), [
                'product_id' => $p->id,
                'quantity' => 2,
            ]);

        $response->assertRedirect(route('jobs.show', $job->id));
        $this->assertDatabaseHas('job_materials', [
            'job_id' => $job->id,
            'product_id' => $p->id,
            'quantity' => 2,
        ]);
    }

    public function test_show_page_displays_material_shortage_badge(): void
    {
        $job = Job::factory()->create(['status' => 'assigned']);
        $p = $this->makeProductWithStock('Coupler', 1);
        app(JobMaterialService::class)->add($job, $p->id, 3);

        $this->actingAs($this->owner())
            ->get(route('jobs.show', $job->id))
            ->assertOk()
            ->assertSee('Material shortage');
    }

    public function test_jobs_list_shows_material_shortage_badge(): void
    {
        $job = Job::factory()->create(['status' => 'assigned']);
        $p = $this->makeProductWithStock('Coil', 0);
        app(JobMaterialService::class)->add($job, $p->id, 2);

        $this->actingAs($this->owner())
            ->get(route('jobs.index'))
            ->assertSee('Shortage');
    }

    public function test_substituting_swaps_the_product_on_that_line_only(): void
    {
        $job = \App\Models\Job::factory()->create(['status' => 'assigned']);
        $orig = \App\Models\Product::create(['name' => 'OEM Lens', 'rate' => '1', 'unit' => 'pcs', 'make' => 'X']);
        $alt = \App\Models\Product::create(['name' => 'Compatible Lens', 'rate' => '1', 'unit' => 'pcs', 'make' => 'Y']);
        $mat = \App\Models\JobMaterial::create(['job_id' => $job->id, 'product_id' => $orig->id, 'quantity' => 1]);

        app(\App\Services\Job\JobMaterialService::class)->substitute($mat->id, $alt->id);

        $this->assertDatabaseHas('job_materials', ['id' => $mat->id, 'product_id' => $alt->id]);
    }

    public function test_owner_can_substitute_via_http(): void
    {
        $owner = \App\Models\User::factory()->create();
        $owner->assignRole('Owner');
        $job = \App\Models\Job::factory()->create(['status' => 'assigned']);
        $orig = \App\Models\Product::create(['name' => 'OEM', 'rate' => '1', 'unit' => 'pcs', 'make' => 'X']);
        $alt = \App\Models\Product::create(['name' => 'Alt', 'rate' => '1', 'unit' => 'pcs', 'make' => 'Y']);
        $mat = \App\Models\JobMaterial::create(['job_id' => $job->id, 'product_id' => $orig->id, 'quantity' => 1]);

        $this->actingAs($owner)->post(route('jobs.materials.substitute', $mat->id), ['new_product_id' => $alt->id])->assertRedirect();
        $this->assertDatabaseHas('job_materials', ['id' => $mat->id, 'product_id' => $alt->id]);
    }
}
