<?php

namespace Tests\Feature;

use App\Models\TallyConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TallyConnectionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_and_decrypts_the_password(): void
    {
        $connection = TallyConnection::create([
            'host' => '192.168.1.50',
            'port' => 9000,
            'company_name' => 'Oracle Machine Tech',
            'username' => 'admin',
            'password' => 'secret-password',
            'status' => 'disconnected',
        ]);

        $fresh = TallyConnection::find($connection->id);

        $this->assertSame('secret-password', $fresh->password);

        $rawPassword = DB::table('tally_connections')->where('id', $connection->id)->value('password');
        $this->assertNotSame('secret-password', $rawPassword);
    }

    public function test_password_is_hidden_from_array_and_json_output(): void
    {
        $connection = TallyConnection::create([
            'host' => '192.168.1.50',
            'port' => 9000,
            'company_name' => 'Oracle Machine Tech',
            'username' => 'admin',
            'password' => 'secret-password',
            'status' => 'disconnected',
        ]);

        $this->assertArrayNotHasKey('password', $connection->toArray());
    }
}
