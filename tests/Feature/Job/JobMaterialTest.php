<?php
namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\JobMaterial;
use App\Models\Product;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobMaterialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_a_job_has_many_materials(): void
    {
        $job = Job::factory()->create();
        $p = Product::create(['name' => 'Belt', 'rate' => '100', 'unit' => 'pcs', 'make' => 'X']);
        JobMaterial::create(['job_id' => $job->id, 'product_id' => $p->id, 'quantity' => 2]);

        $this->assertCount(1, $job->fresh()->materials);
        $this->assertSame(2, $job->materials->first()->quantity);
    }
}
