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
        // Fixed 2026-07-19: the list view rendered $item->name, but
        // quationform only has clientname (no name column) - so the Name
        // cell always rendered blank. Now uses $item->clientname.
        Quation::create(['product_id' => 1, 'clientname' => 'Rajesh Patel', 'email' => 'rajesh@example.com', 'phone' => 9998887776]);

        $response = $this->actingAs($user)->get(route('listqutation'));

        $response->assertOk();
        $response->assertSee('rajesh@example.com');
        $response->assertSee('Rajesh Patel');
    }

    public function test_generatequtation_page_shows_config_for_the_requested_product(): void
    {
        $user = User::factory()->create();
        // Fixed 2026-07-19: the quotation form previously always showed
        // product_id=1's config data regardless of the route's {id}. Now
        // it correctly shows the requested product's own config.
        Fource::create(['product_id' => 1, 'modal' => 'Focus-Model-For-Product-1']);
        Fource::create(['product_id' => 999, 'modal' => 'Focus-Model-For-Product-999']);

        $response = $this->actingAs($user)->get(route('generatequtation', 999));

        $response->assertOk();
        $response->assertSee('Focus-Model-For-Product-999');
        $response->assertDontSee('Focus-Model-For-Product-1');
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

    public function test_co2quationstore_creates_a_quotation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('Co2quationstore'), [
            'product_id' => 1,
            'clientname' => 'CO2 Test Client',
            'companyname' => 'CO2 Test Co',
            'description' => 'CO2 laser unit',
            'amount' => '500000',
        ]);

        $response->assertRedirect(route('listqutation'));
        $this->assertDatabaseHas('quationform', [
            'clientname' => 'CO2 Test Client',
            'companyname' => 'CO2 Test Co',
            'description' => 'CO2 laser unit',
            'amount' => '500000',
        ]);
    }

    public function test_printquation_pdf_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('quation.pdf', 1));

        $response->assertOk();
    }

    public function test_generatequtationstore_saves_a_real_10_digit_phone_number(): void
    {
        // Regression test: quationform.phone was a 32-bit `integer` column
        // (max ~2.1 billion) - any real Indian mobile number (>= 6 billion)
        // overflowed it and 500'd on MySQL. Fixed to `string` via
        // 2026_07_22_000001_change_quationform_phone_to_string.php.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('generatequtationstore'), [
            'product_id' => 1,
            'clientname' => 'Real Phone Client',
            'phone' => '9998887776',
        ]);

        $response->assertRedirect(route('listqutation'));
        $this->assertDatabaseHas('quationform', [
            'clientname' => 'Real Phone Client',
            'phone' => '9998887776',
        ]);
    }

    public function test_generatequtationstore_rejects_an_oversized_field_with_a_clean_422(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('generatequtationstore'), [
            'product_id' => 1,
            'clientname' => str_repeat('A', 300),
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('quationform', ['clientname' => str_repeat('A', 300)]);
    }

    public function test_generatequtationstore_rejects_a_non_numeric_product_id_with_a_clean_422(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('generatequtationstore'), [
            'product_id' => 'not-a-number',
            'clientname' => 'Bad Product Id Client',
        ]);

        $response->assertStatus(422);
    }
}
