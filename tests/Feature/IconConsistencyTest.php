<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IconConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_topbar_has_no_mdi_or_bx_icon_classes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('root'));

        $response->assertOk();
        $response->assertDontSee('class="mdi', false);
        $response->assertDontSee("class='mdi", false);
        $response->assertDontSee('class="bx ', false);
        $response->assertDontSee("class='bx ", false);
        $response->assertDontSee('mdi mdi-', false);
    }
}
