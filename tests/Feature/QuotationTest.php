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

    public function test_generatequtationstore_saves_an_unbounded_number_of_pricing_line_items(): void
    {
        // Regression test: the pricing table used to have exactly 3 fixed
        // description/amount column pairs with no way to add more. Line
        // items now live in their own quotation_items table, so a quote can
        // carry any number of rows - here, 5, more than the old ceiling.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('generatequtationstore'), [
            'product_id' => 1,
            'clientname' => 'Many Items Client',
            'items' => [
                ['description' => 'Machine unit', 'amount' => '500000'],
                ['description' => 'Installation', 'amount' => '10000'],
                ['description' => 'Transport', 'amount' => '5000'],
                ['description' => 'Training', 'amount' => '2000'],
                ['description' => 'Annual maintenance', 'amount' => '15000'],
            ],
        ]);

        $response->assertRedirect(route('listqutation'));
        $quotation = Quation::where('clientname', 'Many Items Client')->firstOrFail();
        $this->assertCount(5, $quotation->items);
        $this->assertDatabaseHas('quotation_items', [
            'quotation_id' => $quotation->id,
            'description' => 'Annual maintenance',
            'amount' => '15000',
        ]);
    }

    public function test_generatequtationstore_skips_blank_pricing_rows(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('generatequtationstore'), [
            'product_id' => 1,
            'clientname' => 'Blank Row Client',
            'items' => [
                ['description' => 'Machine unit', 'amount' => '500000'],
                ['description' => '', 'amount' => ''],
            ],
        ]);

        $response->assertRedirect(route('listqutation'));
        $quotation = Quation::where('clientname', 'Blank Row Client')->firstOrFail();
        $this->assertCount(1, $quotation->items);
    }

    public function test_co2quation_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('co2quation', 1));

        $response->assertOk();
    }

    public function test_generatequtation_page_shows_both_fiber_and_co2_sections_with_fiber_selected_by_default(): void
    {
        // The Fiber and Co2 quotation forms were merged into one page with a
        // Machine Type toggle - both field sections must be present in the
        // markup (JS shows/hides them), with Fiber checked by default since
        // this is the route the sidebar's single "Quotation" link uses.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('generatequtation', 1));

        $response->assertOk();
        $response->assertSee('Standard Configuration List');
        $response->assertSee('WORKING AREA');
        $response->assertSee('id="quotation-type-fiber" name="quotation_type" value="fiber" checked', false);
        $response->assertSee('id="quotation-type-co2" name="quotation_type" value="co2" >', false);
    }

    public function test_generatequtation_laser_cutting_dropdown_uses_its_own_master_data(): void
    {
        // Regression test: the "Laser Cutting Machine" dropdown looped over
        // $softeredetails (the Software Details dropdown's own data) instead
        // of $lasercutting, so it always showed Software options even though
        // a distinct Lasercutting master (with its own "Laser Controller"
        // config screen) already existed and was already being passed to
        // the view unused.
        $user = User::factory()->create();
        \App\Models\Softerwere::create(['product_id' => 1, 'modal' => 'Software-Only-Option']);
        \App\Models\Lasercutting::create(['product_id' => 1, 'modal' => 'Laser-Controller-Option']);

        $response = $this->actingAs($user)->get(route('generatequtation', 1));

        $response->assertOk();
        $response->assertSee('Laser-Controller-Option');
    }

    public function test_generatequtation_page_has_no_tinymce_dependency(): void
    {
        // Regression test: the Notes field used a TinyMCE CDN build with no
        // API key, which showed a "valid API key required" banner and put
        // the editor into read-only mode - Notes was effectively unusable.
        // Replaced with a plain textarea.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('generatequtation', 1));

        $response->assertOk();
        $response->assertDontSee('tinymce', false);
        $response->assertSee('id="quotation-notes"', false);
    }

    public function test_co2quation_page_defaults_to_co2_type_selected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('co2quation', 2));

        $response->assertOk();
        $response->assertSee('id="quotation-type-co2" name="quotation_type" value="co2" checked', false);
        $response->assertSee('id="quotation-type-fiber" name="quotation_type" value="fiber" >', false);
    }

    public function test_generatequtationstore_saves_the_notes_field(): void
    {
        // Regression test: the Notes <textarea> in both source forms had no
        // name attribute, so anything typed there was silently discarded on
        // submit despite the quationform.note column existing. Fixed as part
        // of the Fiber/Co2 merge by adding name="note".
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('generatequtationstore'), [
            'product_id' => 1,
            'clientname' => 'Notes Test Client',
            'note' => 'Customer wants delivery within 2 weeks.',
        ]);

        $response->assertRedirect(route('listqutation'));
        $this->assertDatabaseHas('quationform', [
            'clientname' => 'Notes Test Client',
            'note' => 'Customer wants delivery within 2 weeks.',
        ]);
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

    public function test_printquation_pdf_page_renders_with_the_real_quotation_data(): void
    {
        // Regression test: this page used to be a fully static, hardcoded
        // letter for a different company entirely (wrong name, address,
        // phone, email) that never actually looked at which quotation was
        // requested. print() now fetches the real Quation and shows its
        // real client info, pricing items, and company branding.
        $user = User::factory()->create();
        $quotation = Quation::create([
            'product_id' => 1,
            'clientname' => 'PDF Test Client',
            'companyname' => 'PDF Test Company',
            'companyaddress' => '123 Test Street',
            'gstno' => 'GSTTEST123',
            'bank' => 1,
        ]);
        $quotation->items()->create(['description' => 'Machine unit', 'amount' => '250000']);

        $response = $this->actingAs($user)->get(route('quation.pdf', $quotation->id));

        $response->assertOk();
        $response->assertSee('PDF Test Client');
        $response->assertSee('PDF Test Company');
        $response->assertSee('123 Test Street');
        $response->assertSee('GSTTEST123');
        $response->assertSee('Machine unit');
        $response->assertSee(\App\Support\IndianNumber::format(250000));
        $response->assertSee('info@oraclemachinetech.com');
        $response->assertDontSee('technolinksolution', false);
        $response->assertDontSee('JALASAI ENTERPRISES', false);
    }

    public function test_printquation_pdf_page_returns_404_for_a_missing_quotation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('quation.pdf', 999999));

        $response->assertNotFound();
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
