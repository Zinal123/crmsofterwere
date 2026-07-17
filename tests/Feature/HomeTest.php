<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_dashboard_renders_with_real_stats(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['amountwithtax' => 50000, 'remaining_amount' => 5000]);
        Customer::factory()->create(['invoice_id' => $invoice->id]);
        $product = Product::factory()->create(['name' => 'Fiber Laser Cutting Machine']);
        Invoiceproduct::factory()->create(['invoice_id' => $invoice->id, 'product_name' => $product->id, 'totalamount' => 50000]);

        $response = $this->actingAs($user)->get(route('root'));

        $response->assertOk();
        $response->assertSee('1'); // totalInvoices/totalCustomers count
        $response->assertSee('Fiber Laser Cutting Machine');
    }

    public function test_updateprofile_updates_name_and_email(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->post(route('updateProfile', $user->id), [
            'name' => 'New Name',
            'email' => 'new-email@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new-email@example.com',
        ]);
    }

    public function test_updatepassword_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);

        $response = $this->actingAs($user)->post(route('updatePassword', $user->id), [
            'current_password' => 'wrong-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertOk();
        $response->assertJson(['isSuccess' => false]);
    }

    public function test_updatepassword_succeeds_with_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);

        $response = $this->actingAs($user)->post(route('updatePassword', $user->id), [
            'current_password' => 'correct-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertOk();
        $response->assertJson(['isSuccess' => true]);
        $this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
    }
}
