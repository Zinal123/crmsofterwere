<?php

namespace Tests\Feature\Auditing;

use App\Models\AuditLog;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditableTraitTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_record_logs_one_created_row(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $bank = Bank::create(['bankholdername' => 'Test Holder', 'bankname' => 'Test Bank']);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => 'bank',
            'auditable_id' => $bank->id,
            'action' => 'created',
            'field_name' => null,
            'user_id' => $user->id,
        ]);
        $this->assertCount(1, AuditLog::where('auditable_id', $bank->id)->get());
    }

    public function test_updating_one_field_logs_one_row_with_old_and_new_value(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $bank = Bank::create(['bankholdername' => 'Old Holder', 'bankname' => 'Test Bank']);

        $bank->update(['bankholdername' => 'New Holder']);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $bank->id,
            'action' => 'updated',
            'field_name' => 'bankholdername',
            'old_value' => 'Old Holder',
            'new_value' => 'New Holder',
        ]);
    }

    public function test_updating_three_fields_in_one_save_logs_three_rows(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $bank = Bank::create(['bankholdername' => 'A', 'bankname' => 'B', 'bankifsccode' => 'C']);

        $bank->update(['bankholdername' => 'A2', 'bankname' => 'B2', 'bankifsccode' => 'C2']);

        $this->assertCount(4, AuditLog::where('auditable_id', $bank->id)->get()); // 1 created + 3 updated
        $this->assertCount(3, AuditLog::where('auditable_id', $bank->id)->where('action', 'updated')->get());
    }

    public function test_deleting_a_record_logs_one_deleted_row(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $bank = Bank::create(['bankholdername' => 'Delete Me']);

        $bank->delete();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $bank->id,
            'action' => 'deleted',
        ]);
    }

    public function test_excluded_field_logs_the_change_with_no_values(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);
        $this->actingAs($user);

        $user->update(['password' => bcrypt('new-secret-password')]);

        $row = AuditLog::where('auditable_type', 'user')
            ->where('auditable_id', $user->id)
            ->where('field_name', 'password')
            ->first();

        $this->assertNotNull($row);
        $this->assertNull($row->old_value);
        $this->assertNull($row->new_value);
        $this->assertDatabaseMissing('audit_logs', ['old_value' => 'new-secret-password']);
    }

    public function test_no_authenticated_user_stores_null_user_id_and_does_not_throw(): void
    {
        // Simulates a seeder/console-command write, where there's no request/session.
        $bank = Bank::create(['bankholdername' => 'System Created']);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $bank->id,
            'action' => 'created',
            'user_id' => null,
        ]);
    }
}
