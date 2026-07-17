<?php

namespace Tests\Feature;

use App\Models\Fource;
use App\Models\Quation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationTest extends TestCase
{
    use RefreshDatabase;

    public function test_listqutation_page_renders_with_seeded_quotations(): void
    {
        $user = User::factory()->create();
        // Pre-existing bug, preserved: the list view renders $item->name,
        // but quationform only has clientname (no name column) - so the
        // Name cell always renders blank. email/phone are real columns
        // and do render, which is what this test actually characterizes.
        Quation::create(['product_id' => 1, 'clientname' => 'Rajesh Patel', 'email' => 'rajesh@example.com', 'phone' => 9998887776]);

        $response = $this->actingAs($user)->get(route('listqutation'));

        $response->assertOk();
        $response->assertSee('rajesh@example.com');
        $response->assertDontSee('Rajesh Patel');
    }

    public function test_generatequtation_page_renders_and_only_shows_product_id_1_config(): void
    {
        $user = User::factory()->create();
        // Pre-existing bug, preserved: the quotation form always shows
        // product_id=1's config data regardless of the route's {id} -
        // this locks in that exact (quirky) behavior.
        Fource::create(['product_id' => 1, 'modal' => 'Focus-Model-For-Product-1']);
        Fource::create(['product_id' => 999, 'modal' => 'Focus-Model-For-Product-999']);

        $response = $this->actingAs($user)->get(route('generatequtation', 42));

        $response->assertOk();
        $response->assertSee('Focus-Model-For-Product-1');
        $response->assertDontSee('Focus-Model-For-Product-999');
    }

    public function test_generatequtationstore_creates_a_quotation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('generatequtationstore'), [
            'product_id' => 1,
            'clientname' => 'Test Client',
            'companyname' => 'Test Co',
        ]);

        $response->assertRedirect(route('listqutation'));
        $this->assertDatabaseHas('quationform', [
            'clientname' => 'Test Client',
            'companyname' => 'Test Co',
        ]);
    }

    public function test_co2quation_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('co2quation', 1));

        $response->assertOk();
    }

    public function test_printquation_pdf_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('quation.pdf', 1));

        $response->assertOk();
    }
}
