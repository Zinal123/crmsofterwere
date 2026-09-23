<?php

namespace Tests\Feature;

use App\Models\TallyConnection;
use App\Services\Integration\TallyClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TallyClientTest extends TestCase
{
    private function connection(): TallyConnection
    {
        return new TallyConnection([
            'host' => '192.168.1.50',
            'port' => 9000,
            'company_name' => 'Oracle Machine Tech',
            'username' => 'admin',
            'password' => 'secret',
        ]);
    }

    public function test_it_parses_a_successful_response(): void
    {
        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<RESPONSE><CREATED>1</CREATED><ERRORS>0</ERRORS><LASTVCHID>456</LASTVCHID></RESPONSE>', 200),
        ]);

        $response = (new TallyClient())->post($this->connection(), '<ENVELOPE></ENVELOPE>', 15);

        $this->assertTrue($response->accepted);
        $this->assertSame('456', $response->tallyVoucherId);
        $this->assertNull($response->errorMessage);
    }

    public function test_it_parses_a_rejected_response_with_a_line_error(): void
    {
        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<RESPONSE><CREATED>0</CREATED><ERRORS>1</ERRORS><LINEERROR>Ledger ABC Traders does not exist</LINEERROR></RESPONSE>', 200),
        ]);

        $response = (new TallyClient())->post($this->connection(), '<ENVELOPE></ENVELOPE>', 15);

        $this->assertFalse($response->accepted);
        $this->assertSame('Ledger ABC Traders does not exist', $response->errorMessage);
    }

    public function test_it_reports_a_connection_failure_without_throwing(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection timed out');
        });

        $response = (new TallyClient())->post($this->connection(), '<ENVELOPE></ENVELOPE>', 5);

        $this->assertFalse($response->accepted);
        $this->assertStringContainsString('Could not reach Tally server', $response->errorMessage);
    }

    public function test_it_reports_a_non_2xx_http_response(): void
    {
        Http::fake([
            'http://192.168.1.50:9000' => Http::response('Internal Server Error', 500),
        ]);

        $response = (new TallyClient())->post($this->connection(), '<ENVELOPE></ENVELOPE>', 15);

        $this->assertFalse($response->accepted);
        $this->assertStringContainsString('HTTP 500', $response->errorMessage);
    }
}
