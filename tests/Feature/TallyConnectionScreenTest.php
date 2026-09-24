<?php

namespace Tests\Feature;

use App\Models\TallyConnection;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TallyConnectionScreenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function owner(): User
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        return $owner;
    }

    public function test_owner_can_view_the_connection_screen(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->get(route('tally-connection.edit'))->assertOk();
    }

    public function test_worker_cannot_view_the_connection_screen(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $this->actingAs($worker)->get(route('tally-connection.edit'))->assertForbidden();
    }

    public function test_owner_can_save_connection_details(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->post(route('tally-connection.update'), [
            'host' => '192.168.1.50',
            'port' => 9000,
            'company_name' => 'Oracle Machine Tech',
            'username' => 'admin',
            'password' => 'secret-password',
        ])->assertRedirect(route('tally-connection.edit'));

        $this->assertDatabaseHas('tally_connections', [
            'host' => '192.168.1.50',
            'company_name' => 'Oracle Machine Tech',
            'status' => 'disconnected',
        ]);
    }

    public function test_saving_again_without_a_password_keeps_the_existing_one(): void
    {
        $owner = $this->owner();
        $connection = TallyConnection::create([
            'host' => '192.168.1.50', 'port' => 9000, 'company_name' => 'Oracle Machine Tech',
            'username' => 'admin', 'password' => 'original-password', 'status' => 'connected',
        ]);

        $this->actingAs($owner)->post(route('tally-connection.update'), [
            'host' => '192.168.1.51',
            'port' => 9000,
            'company_name' => 'Oracle Machine Tech',
            'username' => 'admin',
        ])->assertRedirect(route('tally-connection.edit'));

        $connection->refresh();
        $this->assertSame('original-password', $connection->password);
        $this->assertSame('192.168.1.51', $connection->host);
    }

    public function test_connect_button_triggers_a_real_test_and_shows_the_result(): void
    {
        $owner = $this->owner();
        TallyConnection::create([
            'host' => '192.168.1.50', 'port' => 9000, 'company_name' => 'Oracle Machine Tech',
            'username' => 'admin', 'password' => 'secret', 'status' => 'disconnected',
        ]);

        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<ENVELOPE><COMPANY NAME="Oracle Machine Tech"></COMPANY></ENVELOPE>', 200),
        ]);

        $this->actingAs($owner)->post(route('tally-connection.connect'))->assertRedirect(route('tally-connection.edit'));

        $this->assertDatabaseHas('tally_connections', ['status' => 'connected']);
    }
}
