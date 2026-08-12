<?php

namespace Tests\Feature\Inventory;

use App\Models\Invetry;
use App\Models\Job;
use App\Models\JobMaterial;
use App\Models\Product;
use App\Services\Inventory\AvailabilityService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityJobDemandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeProductWithStock(int $onHand): Product
    {
        $p = Product::create(['name' => 'Nozzle', 'rate' => '1', 'unit' => 'pcs', 'make' => 'X']);
        Invetry::create(['product_id' => $p->id, 'quantity' => $onHand, 'vandername' => 'V', 'rate' => '1']);

        return $p;
    }

    public function test_open_job_material_reserves_stock(): void
    {
        $p = $this->makeProductWithStock(10);
        $job = Job::factory()->create(['status' => 'in_progress']);
        JobMaterial::create(['job_id' => $job->id, 'product_id' => $p->id, 'quantity' => 4]);

        $this->assertSame(6, app(AvailabilityService::class)->available($p->id));
    }

    public function test_completed_job_material_does_not_reserve_stock(): void
    {
        $p = $this->makeProductWithStock(10);

        $openJob = Job::factory()->create(['status' => 'in_progress']);
        JobMaterial::create(['job_id' => $openJob->id, 'product_id' => $p->id, 'quantity' => 4]);

        $completedJob = Job::factory()->create(['status' => 'completed']);
        JobMaterial::create(['job_id' => $completedJob->id, 'product_id' => $p->id, 'quantity' => 3]);

        $this->assertSame(6, app(AvailabilityService::class)->available($p->id));
    }

    public function test_excluding_a_job_ignores_its_own_demand(): void
    {
        $p = $this->makeProductWithStock(10);
        $openJob = Job::factory()->create(['status' => 'in_progress']);
        JobMaterial::create(['job_id' => $openJob->id, 'product_id' => $p->id, 'quantity' => 4]);

        $this->assertSame(10, app(AvailabilityService::class)->available($p->id, null, $openJob->id));
    }
}
