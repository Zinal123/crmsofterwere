<?php

namespace Tests\Feature;

use App\Models\TallyConnection;
use App\Services\Integration\TallyConnectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TallyConnectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private function connection(array $overrides = []): TallyConnection
    {
        return TallyConnection::create(array_merge([
            'host' => '192.168.1.50',
            'port' => 9000,
            'company_name' => 'Oracle Machine Tech',
            'username' => 'admin',
            'password' => 'secret',
            'status' => 'disconnected',
        ], $overrides));
    }

    public function test_it_marks_connected_when_the_company_name_comes_back(): void
    {
        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<ENVELOPE><COMPANY NAME="Oracle Machine Tech"></COMPANY></ENVELOPE>', 200),
        ]);

        $connection = $this->connection();
        (new TallyConnectionService())->testConnection($connection);

        $connection->refresh();
        $this->assertSame('connected', $connection->status);
        $this->assertNotNull($connection->last_checked_at);
        $this->assertNull($connection->last_error);
    }

    public function test_it_marks_failed_when_the_company_name_is_not_found(): void
    {
        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<ENVELOPE></ENVELOPE>', 200),
        ]);

        $connection = $this->connection();
        (new TallyConnectionService())->testConnection($connection);

        $connection->refresh();
        $this->assertSame('failed', $connection->status);
        $this->assertStringContainsString('did not return company', $connection->last_error);
    }

    public function test_it_marks_failed_on_a_network_error(): void
    {
        Http::fake(function () {
            throw new ConnectionException('timed out');
        });

        $connection = $this->connection();
        (new TallyConnectionService())->testConnection($connection);

        $connection->refresh();
        $this->assertSame('failed', $connection->status);
        $this->assertStringContainsString('Could not reach Tally server', $connection->last_error);
    }

    public function test_it_marks_failed_on_a_non_2xx_response(): void
    {
        Http::fake([
            'http://192.168.1.50:9000' => Http::response('error', 500),
        ]);

        $connection = $this->connection();
        (new TallyConnectionService())->testConnection($connection);

        $connection->refresh();
        $this->assertSame('failed', $connection->status);
        $this->assertStringContainsString('HTTP 500', $connection->last_error);
    }

    public function test_it_marks_failed_on_a_malformed_host_without_throwing(): void
    {
        $connection = $this->connection([
            'host' => '192.168.1.50 not-a-host',
        ]);

        (new TallyConnectionService())->testConnection($connection);

        $connection->refresh();
        $this->assertSame('failed', $connection->status);
        $this->assertNotNull($connection->last_error);
    }
}
