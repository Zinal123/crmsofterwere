<?php

namespace Tests\Feature\Expenses;

use App\Models\DailyTransaction;
use App\Models\ExpenseCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashBookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_cash_book_shows_a_running_balance_across_payments_and_receipts(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $paymentCategory = ExpenseCategory::create(['name' => 'Diesel / Fuel', 'type' => 'payment', 'party_model' => null]);
        $receiptCategory = ExpenseCategory::create(['name' => 'Other Income', 'type' => 'receipt', 'party_model' => null]);

        DailyTransaction::create(['type' => 'receipt', 'expense_category_id' => $receiptCategory->id, 'amount' => 10000, 'date' => now()->subDays(2), 'payment_mode' => 'cash', 'created_by' => $owner->id]);
        DailyTransaction::create(['type' => 'payment', 'expense_category_id' => $paymentCategory->id, 'amount' => 3000, 'date' => now()->subDay(), 'payment_mode' => 'cash', 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('expenses.cashbook'));

        $response->assertOk();
        // 10,000 in - 3,000 out = 7,000 running balance
        $response->assertSee('7,000');
    }

    public function test_worker_cannot_view_the_cash_book(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('expenses.cashbook'));

        $response->assertForbidden();
    }
}
