# Tally Direct Connection Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the (never-built) local-sync-agent half of the Tally integration with a direct CRM-to-Tally connection — a settings screen to configure/test the connection, an XML voucher builder + poster for Sales invoices, and a scheduled retry sweep — since the CRM can reach the real Tally server over the network directly.

**Architecture:** New `tally_connections` table stores one encrypted-credential connection row. `TallyConnectionService` tests reachability+auth by asking Tally for its company list. `TallyVoucherXmlBuilder` turns an already-built `tally_sync_queue` payload into Tally's Sales-voucher XML (full per-product inventory lines). `TallyClient` posts XML to Tally's gateway and parses the response. `TallyPostingService` orchestrates all three per queue row, called inline right after invoice creation and by a new scheduled command for retries. The already-built `tally_sync_queue` table/model, `TallySyncQueueService`, and the Owner/Account status screen are reused as-is; the already-built agent-polling API (Tasks 4/5 of the prior plan) is left in place, unused.

**Tech Stack:** Laravel 10, MySQL (live) / SQLite (tests), Laravel's `Http` facade (`Illuminate\Support\Facades\Http`) with `Http::fake()` for testing, DOMDocument for XML generation, Spatie Permission (`permission:` middleware, reusing the existing `tally-sync.view` permission), Blade + existing form/card conventions (see `resources/views/profile.blade.php` for the established `card dash-card` + `form-label`/`text-danger` required-marker pattern this plan follows).

## Global Constraints

- No auto-creation of any Tally master data (ledgers or stock items) — if a required ledger/stock item is missing in Tally, the voucher fails with Tally's own message recorded in `last_error`; a human fixes it in Tally and retries. Applies uniformly to customer ledgers, tax ledgers, the Sales ledger, and stock items.
- One-way sync only: CRM → Tally. Nothing in this plan reads business data back from Tally (the connection test only asks for a company list to verify reachability).
- A failed or slow call to Tally must never break invoice creation — the invoice's DB transaction commits before any Tally call is attempted, and any exception from that attempt is caught and logged (`report()`), never propagated.
- No new server infrastructure (no queue-worker daemon) — retries run via Laravel's existing scheduler (already wired to cron for `jobs:flag-overdue`/`inventory:flag-low-stock` in `app/Console/Kernel.php`).
- Password storage: `TallyConnection.password` uses Laravel's `encrypted` Eloquent cast (encrypted at rest in MySQL) and the column is `$hidden` so it never appears in `toArray()`/`toJson()` output or accidental logging.
- Follow this codebase's existing conventions exactly: `permission:` Blade `@can`/middleware (Spatie Permission) for the new web screen — reuse the existing `tally-sync.view` permission (Owner+Account), no new permission needed; `RefreshDatabase` in tests; `syncRoles()` (not `assignRole()`) whenever a test needs a user isolated to exactly one non-Owner role.
- Exact Tally XML tag names for Security-Control authentication, the Sales-voucher envelope, and response parsing are genuinely unconfirmed until tested against the real server (this is called out explicitly in `docs/superpowers/specs/2026-09-23-tally-direct-connection-design.md`'s "Open questions" section) — every task below implements a concrete, structurally-correct, fully unit-tested (via `Http::fake()`) best effort based on Tally's documented XML-import format, with the specific tag names isolated in one place per class so they're easy to adjust after live verification (Task 10).

---

### Task 1: `tally_connections` table + model

**Files:**
- Create: `database/migrations/2026_09_23_000001_create_tally_connections_table.php`
- Create: `app/Models/TallyConnection.php`
- Test: `tests/Feature/TallyConnectionModelTest.php`

**Interfaces:**
- Produces: `App\Models\TallyConnection` — Eloquent model, table `tally_connections`, fillable `host, port, company_name, username, password, status, last_checked_at, last_error`, `password` cast `encrypted` and `$hidden`, `last_checked_at` cast `datetime`.

- [ ] **Step 1: Write the failing test**

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallyConnectionModelTest`
Expected: FAIL — table `tally_connections` / class `TallyConnection` don't exist yet.

- [ ] **Step 3: Write the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tally_connections', function (Blueprint $table) {
            $table->id();
            $table->string('host');
            $table->unsignedInteger('port')->default(9000);
            $table->string('company_name');
            $table->string('username');
            $table->text('password');
            $table->enum('status', ['disconnected', 'connected', 'failed'])->default('disconnected');
            $table->timestamp('last_checked_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tally_connections');
    }
};
```

- [ ] **Step 4: Write the model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TallyConnection extends Model
{
    protected $table = 'tally_connections';

    protected $fillable = [
        'host',
        'port',
        'company_name',
        'username',
        'password',
        'status',
        'last_checked_at',
        'last_error',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'last_checked_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
    ];
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=TallyConnectionModelTest`
Expected: PASS (2 tests)

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_09_23_000001_create_tally_connections_table.php app/Models/TallyConnection.php tests/Feature/TallyConnectionModelTest.php
git commit -m "$(cat <<'EOF'
feat: add tally_connections table and model

Stores one Tally server connection (host/port/company/username/
encrypted password) for the direct-connection integration - replaces
the local-sync-agent's need for per-installation config.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 2: `TallyResponse` DTO + `TallyClient`

**Files:**
- Create: `app/Services/Integration/TallyResponse.php`
- Create: `app/Services/Integration/TallyClient.php`
- Test: `tests/Feature/TallyClientTest.php`

**Interfaces:**
- Consumes: `App\Models\TallyConnection` (Task 1).
- Produces: `App\Services\Integration\TallyResponse` (readonly: `accepted: bool`, `tallyVoucherId: ?string`, `errorMessage: ?string`, `rawBody: ?string`; static `fromXml(string $body): self`, static `networkFailure(string $message): self`). `App\Services\Integration\TallyClient::post(TallyConnection $connection, string $xml, int $timeoutSeconds): TallyResponse` — used by Task 6 (`TallyPostingService`).

- [ ] **Step 1: Write the failing test**

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallyClientTest`
Expected: FAIL — classes `TallyResponse`/`TallyClient` don't exist yet.

- [ ] **Step 3: Write `TallyResponse`**

```php
<?php

namespace App\Services\Integration;

class TallyResponse
{
    public function __construct(
        public readonly bool $accepted,
        public readonly ?string $tallyVoucherId,
        public readonly ?string $errorMessage,
        public readonly ?string $rawBody = null,
    ) {
    }

    /**
     * Parse Tally's XML-import response. Tag names (CREATED/ERRORS/
     * LASTVCHID/LINEERROR/EXCEPTIONS) follow Tally's documented format but
     * are not yet confirmed against this project's real server - see
     * docs/superpowers/specs/2026-09-23-tally-direct-connection-design.md.
     */
    public static function fromXml(string $body): self
    {
        $xml = @simplexml_load_string($body);

        if ($xml === false) {
            return new self(accepted: false, tallyVoucherId: null, errorMessage: 'Tally returned a response that could not be parsed as XML.', rawBody: $body);
        }

        $errors = isset($xml->ERRORS) ? (int) $xml->ERRORS : 0;
        $created = isset($xml->CREATED) ? (int) $xml->CREATED : 0;

        if ($errors > 0 || $created < 1) {
            $message = isset($xml->LINEERROR)
                ? (string) $xml->LINEERROR
                : (isset($xml->EXCEPTIONS) ? (string) $xml->EXCEPTIONS : 'Tally rejected the voucher (no error detail returned).');

            return new self(accepted: false, tallyVoucherId: null, errorMessage: $message, rawBody: $body);
        }

        return new self(
            accepted: true,
            tallyVoucherId: isset($xml->LASTVCHID) ? (string) $xml->LASTVCHID : null,
            errorMessage: null,
            rawBody: $body,
        );
    }

    public static function networkFailure(string $message): self
    {
        return new self(accepted: false, tallyVoucherId: null, errorMessage: $message, rawBody: null);
    }
}
```

- [ ] **Step 4: Write `TallyClient`**

```php
<?php

namespace App\Services\Integration;

use App\Models\TallyConnection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class TallyClient
{
    public function post(TallyConnection $connection, string $xml, int $timeoutSeconds): TallyResponse
    {
        $url = sprintf('http://%s:%d', $connection->host, $connection->port);

        try {
            $response = Http::timeout($timeoutSeconds)->withBody($xml, 'text/xml')->post($url);
        } catch (ConnectionException $e) {
            return TallyResponse::networkFailure('Could not reach Tally server: '.$e->getMessage());
        }

        if ($response->failed()) {
            return TallyResponse::networkFailure('Tally server responded with HTTP '.$response->status().'.');
        }

        return TallyResponse::fromXml($response->body());
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=TallyClientTest`
Expected: PASS (4 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Services/Integration/TallyResponse.php app/Services/Integration/TallyClient.php tests/Feature/TallyClientTest.php
git commit -m "$(cat <<'EOF'
feat: add TallyResponse DTO and TallyClient HTTP wrapper

TallyClient posts raw XML to a Tally connection's gateway and never
throws on network failure - it returns a TallyResponse either way, so
callers always get a value to record, not an exception to catch.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 3: `TallyConnectionService::testConnection()`

**Files:**
- Create: `app/Services/Integration/TallyConnectionService.php`
- Test: `tests/Feature/TallyConnectionServiceTest.php`

**Interfaces:**
- Consumes: `App\Models\TallyConnection` (Task 1).
- Produces: `App\Services\Integration\TallyConnectionService::testConnection(TallyConnection $connection): void` — updates the passed connection's `status`/`last_checked_at`/`last_error` in place. Used by Task 4's controller.

- [ ] **Step 1: Write the failing test**

```php
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

    private function connection(): TallyConnection
    {
        return TallyConnection::create([
            'host' => '192.168.1.50',
            'port' => 9000,
            'company_name' => 'Oracle Machine Tech',
            'username' => 'admin',
            'password' => 'secret',
            'status' => 'disconnected',
        ]);
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
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallyConnectionServiceTest`
Expected: FAIL — class `TallyConnectionService` doesn't exist yet.

- [ ] **Step 3: Write the service**

```php
<?php

namespace App\Services\Integration;

use App\Models\TallyConnection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class TallyConnectionService
{
    private const TIMEOUT_SECONDS = 5;

    /**
     * Send a lightweight "list of companies" request and judge success by
     * whether the configured company name comes back in Tally's response.
     * The exact request/response shape (including how Security Control
     * credentials are actually authenticated) needs live verification
     * against the real server - see
     * docs/superpowers/specs/2026-09-23-tally-direct-connection-design.md.
     */
    public function testConnection(TallyConnection $connection): void
    {
        $xml = $this->buildListCompaniesRequest($connection);
        $url = sprintf('http://%s:%d', $connection->host, $connection->port);

        try {
            $httpResponse = Http::timeout(self::TIMEOUT_SECONDS)->withBody($xml, 'text/xml')->post($url);
        } catch (ConnectionException $e) {
            $this->markFailed($connection, 'Could not reach Tally server: '.$e->getMessage());

            return;
        }

        if ($httpResponse->failed()) {
            $this->markFailed($connection, 'Tally server responded with HTTP '.$httpResponse->status().'.');

            return;
        }

        if (! str_contains(strtolower($httpResponse->body()), strtolower($connection->company_name))) {
            $this->markFailed($connection, "Tally did not return company \"{$connection->company_name}\" - check the company name and credentials.");

            return;
        }

        $connection->update([
            'status' => 'connected',
            'last_checked_at' => now(),
            'last_error' => null,
        ]);
    }

    private function markFailed(TallyConnection $connection, string $message): void
    {
        $connection->update([
            'status' => 'failed',
            'last_checked_at' => now(),
            'last_error' => $message,
        ]);
    }

    private function buildListCompaniesRequest(TallyConnection $connection): string
    {
        $dom = new \DOMDocument('1.0', 'utf-8');

        $envelope = $dom->createElement('ENVELOPE');
        $dom->appendChild($envelope);

        $header = $dom->createElement('HEADER');
        $header->appendChild($dom->createElement('TALLYREQUEST', 'Export'));
        $envelope->appendChild($header);

        $requestDesc = $dom->createElement('REQUESTDESC');
        $requestDesc->appendChild($dom->createElement('REPORTNAME', 'List of Companies'));

        $staticVariables = $dom->createElement('STATICVARIABLES');
        $staticVariables->appendChild($dom->createElement('SVCURRENTUSER', htmlspecialchars($connection->username, ENT_XML1)));
        $staticVariables->appendChild($dom->createElement('SVCURRENTUSERPASSWORD', htmlspecialchars($connection->password, ENT_XML1)));
        $requestDesc->appendChild($staticVariables);

        $exportData = $dom->createElement('EXPORTDATA');
        $exportData->appendChild($requestDesc);

        $body = $dom->createElement('BODY');
        $body->appendChild($exportData);
        $envelope->appendChild($body);

        return $dom->saveXML();
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=TallyConnectionServiceTest`
Expected: PASS (4 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Services/Integration/TallyConnectionService.php tests/Feature/TallyConnectionServiceTest.php
git commit -m "$(cat <<'EOF'
feat: add TallyConnectionService to test Tally reachability + auth

Sends a lightweight "list of companies" XML request and judges
success by whether the configured company name comes back - the
minimal check that proves the host/port/credentials actually work.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 4: "Tally Connection" settings screen

**Files:**
- Create: `app/Http/Controllers/Integration/TallyConnectionController.php`
- Create: `resources/views/integration/tally-connection/edit.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/sidebar.blade.php`
- Test: `tests/Feature/TallyConnectionScreenTest.php`

**Interfaces:**
- Consumes: `App\Models\TallyConnection` (Task 1), `App\Services\Integration\TallyConnectionService::testConnection()` (Task 3), existing `tally-sync.view` permission.
- Produces: `GET /tally-connection` (named `tally-connection.edit`), `POST /tally-connection` (named `tally-connection.update`), `POST /tally-connection/connect` (named `tally-connection.connect`) — all permission-gated on `tally-sync.view`.

- [ ] **Step 1: Write the failing test**

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallyConnectionScreenTest`
Expected: FAIL — routes don't exist yet.

- [ ] **Step 3: Write the controller**

```php
<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\TallyConnection;
use App\Services\Integration\TallyConnectionService;
use Illuminate\Http\Request;

class TallyConnectionController extends Controller
{
    public function edit()
    {
        return view('integration.tally-connection.edit', [
            'connection' => TallyConnection::first(),
        ]);
    }

    public function update(Request $request)
    {
        $existing = TallyConnection::first();

        $data = $request->validate([
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'company_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => ($existing ? 'nullable' : 'required').'|string|max:255',
        ]);

        $connection = $existing ?? new TallyConnection();
        $connection->host = $data['host'];
        $connection->port = $data['port'];
        $connection->company_name = $data['company_name'];
        $connection->username = $data['username'];

        if (! empty($data['password'])) {
            $connection->password = $data['password'];
        }

        $connection->status = 'disconnected';
        $connection->save();

        return redirect()->route('tally-connection.edit')->with('success', 'Tally connection details saved.');
    }

    public function connect(TallyConnectionService $service)
    {
        $connection = TallyConnection::first();

        if (! $connection) {
            return redirect()->route('tally-connection.edit')->with('error', 'Save connection details before testing the connection.');
        }

        $service->testConnection($connection);
        $connection->refresh();

        return redirect()->route('tally-connection.edit')->with(
            $connection->status === 'connected' ? 'success' : 'error',
            $connection->status === 'connected' ? 'Connected to Tally successfully.' : 'Connection failed: '.$connection->last_error
        );
    }
}
```

- [ ] **Step 4: Add the routes**

In `routes/web.php`, replace the existing `tally-sync.view` middleware group (currently just the two Tally Sync routes):

```php
    Route::middleware('permission:tally-sync.view')->group(function () {
        Route::get('tally-sync', [App\Http\Controllers\Integration\TallySyncController::class, 'index'])->name('tally-sync.index');
        Route::post('tally-sync/{tallySyncQueue}/retry', [App\Http\Controllers\Integration\TallySyncController::class, 'retry'])->name('tally-sync.retry');
    });
```

with:

```php
    Route::middleware('permission:tally-sync.view')->group(function () {
        Route::get('tally-sync', [App\Http\Controllers\Integration\TallySyncController::class, 'index'])->name('tally-sync.index');
        Route::post('tally-sync/{tallySyncQueue}/retry', [App\Http\Controllers\Integration\TallySyncController::class, 'retry'])->name('tally-sync.retry');
        Route::get('tally-connection', [App\Http\Controllers\Integration\TallyConnectionController::class, 'edit'])->name('tally-connection.edit');
        Route::post('tally-connection', [App\Http\Controllers\Integration\TallyConnectionController::class, 'update'])->name('tally-connection.update');
        Route::post('tally-connection/connect', [App\Http\Controllers\Integration\TallyConnectionController::class, 'connect'])->name('tally-connection.connect');
    });
```

- [ ] **Step 5: Write the view**

```blade
@extends('layouts.master')
@section('title') Tally Connection @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Integration @endslot
@slot('title') Tally Connection @endslot
@endcomponent

<div class="row">
    <div class="col-xl-8">
        <div class="card dash-card">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tally Server Connection</h5>
                @if ($connection)
                    @php
                        $badge = $connection->status === 'connected' ? 'success' : ($connection->status === 'failed' ? 'danger' : 'secondary');
                    @endphp
                    <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }}">{{ ucfirst($connection->status) }}</span>
                @endif
            </div>
            <div class="card-body">
                @if ($connection?->last_error)
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line align-middle me-1"></i>{{ $connection->last_error }}
                </div>
                @endif
                @if ($connection?->status === 'connected')
                <div class="alert alert-success">
                    <i class="ri-checkbox-circle-line align-middle me-1"></i>Connected &mdash; last checked {{ $connection->last_checked_at->diffForHumans() }}.
                </div>
                @endif

                <form action="{{ route('tally-connection.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="host" class="form-label">Server Host / IP <span class="text-danger">*</span></label>
                            <input type="text" id="host" name="host" class="form-control @error('host') is-invalid @enderror" value="{{ old('host', $connection?->host) }}" required>
                            @error('host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="port" class="form-label">Port <span class="text-danger">*</span></label>
                            <input type="number" id="port" name="port" class="form-control @error('port') is-invalid @enderror" value="{{ old('port', $connection?->port ?? 9000) }}" required>
                            @error('port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="company_name" class="form-label">Tally Company Name <span class="text-danger">*</span></label>
                            <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $connection?->company_name) }}" required>
                            @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Tally Username <span class="text-danger">*</span></label>
                            <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $connection?->username) }}" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Tally Password {!! $connection ? '' : '<span class="text-danger">*</span>' !!}</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ $connection ? 'Leave blank to keep current password' : '' }}" {{ $connection ? '' : 'required' }}>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="ri-save-line align-middle me-1"></i> Save</button>
                        </div>
                    </div>
                </form>

                @if ($connection)
                <form action="{{ route('tally-connection.connect') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary"><i class="ri-plug-line align-middle me-1"></i> Connect</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 6: Add the sidebar link**

In `resources/views/layouts/sidebar.blade.php`, right after the existing `tally-sync.view` block:

```blade
                @can('tally-sync.view')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('tally-sync.*') ? 'active' : '' }}" href="{{ route('tally-sync.index') }}">
                        <i class="ri-exchange-line"></i> <span>Tally Sync</span>
                    </a>
                </li>
                @endcan
```

add:

```blade
                @can('tally-sync.view')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('tally-connection.*') ? 'active' : '' }}" href="{{ route('tally-connection.edit') }}">
                        <i class="ri-plug-line"></i> <span>Tally Connection</span>
                    </a>
                </li>
                @endcan
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=TallyConnectionScreenTest`
Expected: PASS (5 tests)

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Integration/TallyConnectionController.php resources/views/integration/tally-connection/edit.blade.php routes/web.php resources/views/layouts/sidebar.blade.php tests/Feature/TallyConnectionScreenTest.php
git commit -m "$(cat <<'EOF'
feat: add Tally Connection settings screen

Owner/Account screen to configure host/port/company/credentials and
test the connection - no OAuth redirect (Tally has no such flow), a
plain form plus a Connect button that runs a real reachability+auth
check via TallyConnectionService.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 5: `TallyVoucherXmlBuilder`

**Files:**
- Modify: `config/tally.php`
- Modify: `.env.example`
- Create: `app/Services/Integration/TallyVoucherXmlBuilder.php`
- Test: `tests/Feature/TallyVoucherXmlBuilderTest.php`

**Interfaces:**
- Consumes: a `tally_sync_queue.payload` array shaped exactly as `TallySyncQueueService::enqueueSalesInvoice()` already builds it (keys: `invoice_id`, `invoice_number`, `invoice_date`, `customer.name`, `total_before_tax`, `total_with_tax`, `sgst_amount`, `cgst_amount`, `igst_amount`, `line_items[].{product_name,hsn,unit,quantity,rate,gst_percent,gst_amount,total_amount}`).
- Produces: `App\Services\Integration\TallyVoucherXmlBuilder::buildSalesVoucher(array $payload, string $companyName): string` — used by Task 6 (`TallyPostingService`).

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Services\Integration\TallyVoucherXmlBuilder;
use Tests\TestCase;

class TallyVoucherXmlBuilderTest extends TestCase
{
    private function payload(): array
    {
        return [
            'invoice_id' => 1,
            'invoice_number' => 'INV-001',
            'invoice_date' => '2026-09-22',
            'customer' => ['name' => 'Test Customer'],
            'total_before_tax' => 50000.0,
            'total_with_tax' => 59000.0,
            'sgst_amount' => 4500.0,
            'cgst_amount' => 4500.0,
            'igst_amount' => 0.0,
            'line_items' => [
                [
                    'product_name' => 'Widget X',
                    'hsn' => '8456',
                    'unit' => 'Nos',
                    'quantity' => 1,
                    'rate' => 50000,
                    'gst_percent' => 18,
                    'gst_amount' => 9000,
                    'total_amount' => 50000,
                ],
            ],
        ];
    }

    private function configureLedgers(): void
    {
        config([
            'tally.ledgers.sales' => 'Sales Account',
            'tally.ledgers.cgst' => 'CGST',
            'tally.ledgers.sgst' => 'SGST',
            'tally.ledgers.igst' => 'IGST',
        ]);
    }

    public function test_it_builds_a_voucher_with_the_right_party_and_reference(): void
    {
        $this->configureLedgers();

        $xml = (new TallyVoucherXmlBuilder())->buildSalesVoucher($this->payload(), 'Oracle Machine Tech');
        $doc = simplexml_load_string($xml);
        $voucher = $doc->BODY->IMPORTDATA->REQUESTDATA->TALLYMESSAGE->VOUCHER;

        $this->assertSame('Sales', (string) $voucher['VCHTYPE']);
        $this->assertSame('INV-001', (string) $voucher->VOUCHERNUMBER);
        $this->assertSame('SALES-1', (string) $voucher->REFERENCE);
        $this->assertSame('Test Customer', (string) $voucher->PARTYLEDGERNAME);
    }

    public function test_ledger_entries_balance_to_zero(): void
    {
        $this->configureLedgers();

        $xml = (new TallyVoucherXmlBuilder())->buildSalesVoucher($this->payload(), 'Oracle Machine Tech');
        $doc = simplexml_load_string($xml);
        $voucher = $doc->BODY->IMPORTDATA->REQUESTDATA->TALLYMESSAGE->VOUCHER;

        $total = 0.0;
        foreach ($voucher->{'ALLLEDGERENTRIES.LIST'} as $entry) {
            $total += (float) $entry->AMOUNT;
        }

        $this->assertEqualsWithDelta(0.0, $total, 0.001);
    }

    public function test_it_includes_one_inventory_entry_per_line_item_with_the_real_product_name(): void
    {
        $this->configureLedgers();

        $xml = (new TallyVoucherXmlBuilder())->buildSalesVoucher($this->payload(), 'Oracle Machine Tech');
        $doc = simplexml_load_string($xml);
        $voucher = $doc->BODY->IMPORTDATA->REQUESTDATA->TALLYMESSAGE->VOUCHER;

        $inventoryEntries = $voucher->{'ALLINVENTORYENTRIES.LIST'};
        $this->assertCount(1, $inventoryEntries);
        $this->assertSame('Widget X', (string) $inventoryEntries[0]->STOCKITEMNAME);
        $this->assertSame('50000.00', (string) $inventoryEntries[0]->AMOUNT);
    }

    public function test_it_omits_the_igst_ledger_entry_when_the_amount_is_zero(): void
    {
        $this->configureLedgers();

        $xml = (new TallyVoucherXmlBuilder())->buildSalesVoucher($this->payload(), 'Oracle Machine Tech');
        $doc = simplexml_load_string($xml);
        $voucher = $doc->BODY->IMPORTDATA->REQUESTDATA->TALLYMESSAGE->VOUCHER;

        $ledgerNames = [];
        foreach ($voucher->{'ALLLEDGERENTRIES.LIST'} as $entry) {
            $ledgerNames[] = (string) $entry->LEDGERNAME;
        }

        $this->assertNotContains('IGST', $ledgerNames);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallyVoucherXmlBuilderTest`
Expected: FAIL — class `TallyVoucherXmlBuilder` doesn't exist yet.

- [ ] **Step 3: Add the ledger config**

Replace `config/tally.php`'s full contents with:

```php
<?php

return [
    // The CRM's own business details, embedded into every Sales voucher
    // payload sent to Tally - static, not per-invoice data.
    'company' => [
        'name' => env('TALLY_COMPANY_NAME'),
        'gstin' => env('TALLY_COMPANY_GSTIN'),
        'state' => env('TALLY_COMPANY_STATE', 'Gujarat'),
    ],

    // Tally ledger names these vouchers post against - must already exist
    // in Tally exactly as named here (see the "fail, don't auto-create"
    // decision in docs/superpowers/specs/2026-09-23-tally-direct-connection-design.md).
    'ledgers' => [
        'sales' => env('TALLY_SALES_LEDGER', 'Sales Account'),
        'cgst' => env('TALLY_CGST_LEDGER', 'CGST'),
        'sgst' => env('TALLY_SGST_LEDGER', 'SGST'),
        'igst' => env('TALLY_IGST_LEDGER', 'IGST'),
    ],
];
```

- [ ] **Step 4: Add the env keys**

Add to `.env.example` (new lines, end of file):

```
TALLY_SALES_LEDGER="Sales Account"
TALLY_CGST_LEDGER=CGST
TALLY_SGST_LEDGER=SGST
TALLY_IGST_LEDGER=IGST
```

- [ ] **Step 5: Write the builder**

```php
<?php

namespace App\Services\Integration;

class TallyVoucherXmlBuilder
{
    /**
     * Build the Tally XML envelope to import a Sales voucher from a
     * tally_sync_queue payload (see TallySyncQueueService::enqueueSalesInvoice()).
     *
     * Ledger entry signs (ISDEEMEDPOSITIVE / AMOUNT) follow Tally's
     * documented Sales-voucher-with-inventory convention, but have not yet
     * been confirmed against this project's real Tally server - see the
     * "Open questions" section of
     * docs/superpowers/specs/2026-09-23-tally-direct-connection-design.md.
     * The one structural property that IS guaranteed regardless of sign
     * convention: all ALLLEDGERENTRIES.LIST amounts sum to zero (Tally
     * requires this for any voucher to balance).
     */
    public function buildSalesVoucher(array $payload, string $companyName): string
    {
        $dom = new \DOMDocument('1.0', 'utf-8');

        $envelope = $dom->createElement('ENVELOPE');
        $dom->appendChild($envelope);

        $header = $dom->createElement('HEADER');
        $header->appendChild($dom->createElement('TALLYREQUEST', 'Import Data'));
        $envelope->appendChild($header);

        $requestDesc = $dom->createElement('REQUESTDESC');
        $requestDesc->appendChild($dom->createElement('REPORTNAME', 'Vouchers'));
        $staticVariables = $dom->createElement('STATICVARIABLES');
        $staticVariables->appendChild($dom->createElement('SVCURRENTCOMPANY', htmlspecialchars($companyName, ENT_XML1)));
        $requestDesc->appendChild($staticVariables);

        $importData = $dom->createElement('IMPORTDATA');
        $importData->appendChild($requestDesc);

        $tallyMessage = $dom->createElement('TALLYMESSAGE');
        $tallyMessage->setAttribute('xmlns:UDF', 'TallyUDF');
        $tallyMessage->appendChild($this->buildVoucher($dom, $payload));

        $requestData = $dom->createElement('REQUESTDATA');
        $requestData->appendChild($tallyMessage);
        $importData->appendChild($requestData);

        $body = $dom->createElement('BODY');
        $body->appendChild($importData);
        $envelope->appendChild($body);

        return $dom->saveXML();
    }

    private function buildVoucher(\DOMDocument $dom, array $payload): \DOMElement
    {
        $voucher = $dom->createElement('VOUCHER');
        $voucher->setAttribute('VCHTYPE', 'Sales');
        $voucher->setAttribute('ACTION', 'Create');

        $voucher->appendChild($dom->createElement('DATE', $this->tallyDate($payload['invoice_date'])));
        $voucher->appendChild($dom->createElement('VOUCHERTYPENAME', 'Sales'));
        $voucher->appendChild($dom->createElement('VOUCHERNUMBER', htmlspecialchars((string) $payload['invoice_number'], ENT_XML1)));
        $voucher->appendChild($dom->createElement('REFERENCE', 'SALES-'.$payload['invoice_id']));
        $customerName = (string) ($payload['customer']['name'] ?? '');
        $voucher->appendChild($dom->createElement('PARTYLEDGERNAME', htmlspecialchars($customerName, ENT_XML1)));

        $totalWithTax = (float) $payload['total_with_tax'];
        $totalBeforeTax = (float) $payload['total_before_tax'];

        $voucher->appendChild($this->ledgerEntry($dom, $customerName, true, -$totalWithTax));
        $voucher->appendChild($this->ledgerEntry($dom, (string) config('tally.ledgers.sales'), false, $totalBeforeTax));

        if ((float) $payload['sgst_amount'] > 0) {
            $voucher->appendChild($this->ledgerEntry($dom, (string) config('tally.ledgers.sgst'), false, (float) $payload['sgst_amount']));
        }
        if ((float) $payload['cgst_amount'] > 0) {
            $voucher->appendChild($this->ledgerEntry($dom, (string) config('tally.ledgers.cgst'), false, (float) $payload['cgst_amount']));
        }
        if ((float) $payload['igst_amount'] > 0) {
            $voucher->appendChild($this->ledgerEntry($dom, (string) config('tally.ledgers.igst'), false, (float) $payload['igst_amount']));
        }

        foreach ($payload['line_items'] as $line) {
            $voucher->appendChild($this->inventoryEntry($dom, $line));
        }

        return $voucher;
    }

    private function ledgerEntry(\DOMDocument $dom, string $ledgerName, bool $isDeemedPositive, float $amount): \DOMElement
    {
        $entry = $dom->createElement('ALLLEDGERENTRIES.LIST');
        $entry->appendChild($dom->createElement('LEDGERNAME', htmlspecialchars($ledgerName, ENT_XML1)));
        $entry->appendChild($dom->createElement('ISDEEMEDPOSITIVE', $isDeemedPositive ? 'Yes' : 'No'));
        $entry->appendChild($dom->createElement('AMOUNT', number_format($amount, 2, '.', '')));

        return $entry;
    }

    private function inventoryEntry(\DOMDocument $dom, array $line): \DOMElement
    {
        $unit = htmlspecialchars((string) ($line['unit'] ?? ''), ENT_XML1);
        $quantity = (float) $line['quantity'];
        $rate = (float) $line['rate'];

        $entry = $dom->createElement('ALLINVENTORYENTRIES.LIST');
        $entry->appendChild($dom->createElement('STOCKITEMNAME', htmlspecialchars((string) $line['product_name'], ENT_XML1)));
        $entry->appendChild($dom->createElement('ISDEEMEDPOSITIVE', 'No'));
        $entry->appendChild($dom->createElement('RATE', number_format($rate, 2, '.', '').'/'.$unit));
        $entry->appendChild($dom->createElement('AMOUNT', number_format((float) $line['total_amount'], 2, '.', '')));

        $batchAllocation = $dom->createElement('BATCHALLOCATIONS.LIST');
        $batchAllocation->appendChild($dom->createElement('ACTUALQTY', number_format($quantity, 2, '.', '').' '.$unit));
        $batchAllocation->appendChild($dom->createElement('BILLEDQTY', number_format($quantity, 2, '.', '').' '.$unit));
        $entry->appendChild($batchAllocation);

        return $entry;
    }

    private function tallyDate(string $date): string
    {
        return date('Ymd', strtotime($date));
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=TallyVoucherXmlBuilderTest`
Expected: PASS (4 tests)

- [ ] **Step 7: Commit**

```bash
git add config/tally.php .env.example app/Services/Integration/TallyVoucherXmlBuilder.php tests/Feature/TallyVoucherXmlBuilderTest.php
git commit -m "$(cat <<'EOF'
feat: add TallyVoucherXmlBuilder for Sales vouchers with inventory lines

Turns a tally_sync_queue payload into Tally's Sales-voucher import XML,
including one inventory entry per line item (full per-product detail,
per the explicit choice to track inventory in Tally). Sales/tax ledger
names are configurable (config/tally.php) since they must match
whatever the accountant's real chart of accounts uses.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 6: `TallyPostingService`

**Files:**
- Create: `app/Services/Integration/TallyPostingService.php`
- Test: `tests/Feature/TallyPostingServiceTest.php`

**Interfaces:**
- Consumes: `App\Models\TallyConnection` (Task 1), `App\Services\Integration\TallyVoucherXmlBuilder::buildSalesVoucher()` (Task 5), `App\Services\Integration\TallyClient::post()` (Task 2), `App\Models\TallySyncQueue` (already built).
- Produces: `App\Services\Integration\TallyPostingService::attempt(TallySyncQueue $queue): void` — used by Task 7 (`InvoiceService`) and Task 8 (scheduled command).

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\TallyConnection;
use App\Models\TallySyncQueue;
use App\Services\Integration\TallyPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TallyPostingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function connection(string $status = 'connected'): TallyConnection
    {
        return TallyConnection::create([
            'host' => '192.168.1.50', 'port' => 9000, 'company_name' => 'Oracle Machine Tech',
            'username' => 'admin', 'password' => 'secret', 'status' => $status,
        ]);
    }

    private function queue(): TallySyncQueue
    {
        return TallySyncQueue::create([
            'source_type' => 'App\\Models\\Invoice',
            'source_id' => 1,
            'voucher_type' => 'sales',
            'reference_no' => 'SALES-1',
            'payload' => [
                'invoice_id' => 1, 'invoice_number' => 'INV-001', 'invoice_date' => '2026-09-22',
                'customer' => ['name' => 'Test Customer'],
                'total_before_tax' => 50000.0, 'total_with_tax' => 59000.0,
                'sgst_amount' => 4500.0, 'cgst_amount' => 4500.0, 'igst_amount' => 0.0,
                'line_items' => [[
                    'product_name' => 'Widget X', 'hsn' => '8456', 'unit' => 'Nos',
                    'quantity' => 1, 'rate' => 50000, 'gst_percent' => 18,
                    'gst_amount' => 9000, 'total_amount' => 50000,
                ]],
            ],
            'status' => 'pending',
        ]);
    }

    public function test_it_marks_synced_when_tally_accepts_the_voucher(): void
    {
        $this->connection();
        $queue = $this->queue();

        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<RESPONSE><CREATED>1</CREATED><ERRORS>0</ERRORS><LASTVCHID>999</LASTVCHID></RESPONSE>', 200),
        ]);

        app(TallyPostingService::class)->attempt($queue);
        $queue->refresh();

        $this->assertSame('synced', $queue->status);
        $this->assertSame('999', $queue->tally_voucher_id);
        $this->assertSame(1, $queue->attempts);
    }

    public function test_it_marks_failed_with_tallys_error_message(): void
    {
        $this->connection();
        $queue = $this->queue();

        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<RESPONSE><CREATED>0</CREATED><ERRORS>1</ERRORS><LINEERROR>Ledger Test Customer does not exist</LINEERROR></RESPONSE>', 200),
        ]);

        app(TallyPostingService::class)->attempt($queue);
        $queue->refresh();

        $this->assertSame('failed', $queue->status);
        $this->assertSame('Ledger Test Customer does not exist', $queue->last_error);
        $this->assertSame(1, $queue->attempts);
    }

    public function test_it_leaves_the_row_pending_when_no_connection_is_configured(): void
    {
        $queue = $this->queue();

        Http::fake();
        app(TallyPostingService::class)->attempt($queue);

        Http::assertNothingSent();
        $queue->refresh();
        $this->assertSame('pending', $queue->status);
        $this->assertSame(0, $queue->attempts);
    }

    public function test_it_leaves_the_row_pending_when_the_connection_is_not_yet_verified(): void
    {
        $this->connection('disconnected');
        $queue = $this->queue();

        Http::fake();
        app(TallyPostingService::class)->attempt($queue);

        Http::assertNothingSent();
        $queue->refresh();
        $this->assertSame('pending', $queue->status);
    }

    public function test_it_does_not_retry_a_row_that_already_hit_the_attempt_cap(): void
    {
        $this->connection();
        $queue = $this->queue();
        $queue->update(['status' => 'failed', 'attempts' => 5]);

        Http::fake();
        app(TallyPostingService::class)->attempt($queue);

        Http::assertNothingSent();
        $queue->refresh();
        $this->assertSame(5, $queue->attempts);
    }

    public function test_it_skips_an_already_synced_row(): void
    {
        $this->connection();
        $queue = $this->queue();
        $queue->update(['status' => 'synced', 'attempts' => 1]);

        Http::fake();
        app(TallyPostingService::class)->attempt($queue);

        Http::assertNothingSent();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallyPostingServiceTest`
Expected: FAIL — class `TallyPostingService` doesn't exist yet.

- [ ] **Step 3: Write the service**

```php
<?php

namespace App\Services\Integration;

use App\Models\TallyConnection;
use App\Models\TallySyncQueue;

class TallyPostingService
{
    private const TIMEOUT_SECONDS = 15;
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private TallyVoucherXmlBuilder $builder,
        private TallyClient $client,
    ) {
    }

    /**
     * Attempt to post one queue row to Tally. Safe to call even when no
     * connection is configured or the connection isn't "connected" yet -
     * the row is simply left as-is in that case, no error recorded.
     */
    public function attempt(TallySyncQueue $queue): void
    {
        if ($queue->status === 'synced') {
            return;
        }

        if ($queue->status === 'failed' && $queue->attempts >= self::MAX_ATTEMPTS) {
            return;
        }

        $connection = TallyConnection::where('status', 'connected')->first();

        if (! $connection) {
            return;
        }

        $xml = $this->builder->buildSalesVoucher($queue->payload, $connection->company_name);
        $response = $this->client->post($connection, $xml, self::TIMEOUT_SECONDS);

        if ($response->accepted) {
            $queue->update([
                'status' => 'synced',
                'tally_voucher_id' => $response->tallyVoucherId,
                'last_error' => null,
                'attempts' => $queue->attempts + 1,
            ]);

            return;
        }

        $queue->update([
            'status' => 'failed',
            'last_error' => $response->errorMessage,
            'attempts' => $queue->attempts + 1,
        ]);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=TallyPostingServiceTest`
Expected: PASS (6 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Services/Integration/TallyPostingService.php tests/Feature/TallyPostingServiceTest.php
git commit -m "$(cat <<'EOF'
feat: add TallyPostingService to post queue rows to Tally

Orchestrates TallyVoucherXmlBuilder + TallyClient for one
tally_sync_queue row: skips synced rows and failed rows over the
attempt cap, leaves the row pending if no connection is configured
or verified yet, otherwise posts and records the real outcome.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 7: Wire `TallyPostingService` into invoice creation

**Files:**
- Modify: `app/Services/Invoice/InvoiceService.php`
- Test: `tests/Feature/TallyInvoicePostingIntegrationTest.php`

**Interfaces:**
- Consumes: `App\Services\Integration\TallyPostingService::attempt(TallySyncQueue $queue): void` (Task 6).

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TallyConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TallyInvoicePostingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function postInvoice(User $user, Product $product): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)->post(route('invoice.store'), [
            'invoice_id' => 'INV-TALLY-POST-001',
            'invoice_date' => '2026-09-22',
            'placesupply' => 'Gujarat',
            'billing_address_full_name' => 'Tally Post Test Customer',
            'billing_state' => 'Gujarat',
            'shipping_state' => 'Gujarat',
            'order_summary_cart_total' => 59000,
            'order_summary_cart_amount' => 50000,
            'new_product_obj' => [
                [
                    'product_name' => $product->id,
                    'hsn' => '8456',
                    'unit' => 'Nos',
                    'product_rate' => 50000,
                    'product_qty' => 1,
                    'product_price' => 50000,
                    'gst' => 18,
                    'withtax' => 9000,
                    'total' => 59000,
                ],
            ],
        ]);
    }

    public function test_invoice_creation_still_succeeds_with_no_tally_connection_configured(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->postInvoice($user, $product);

        $response->assertOk();
        $this->assertDatabaseHas('tally_sync_queue', ['status' => 'pending']);
    }

    public function test_invoice_posts_to_tally_immediately_when_a_connection_is_verified(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        TallyConnection::create([
            'host' => '192.168.1.50', 'port' => 9000, 'company_name' => 'Oracle Machine Tech',
            'username' => 'admin', 'password' => 'secret', 'status' => 'connected',
        ]);

        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<RESPONSE><CREATED>1</CREATED><ERRORS>0</ERRORS><LASTVCHID>1</LASTVCHID></RESPONSE>', 200),
        ]);

        $response = $this->postInvoice($user, $product);

        $response->assertOk();
        $this->assertDatabaseHas('tally_sync_queue', ['status' => 'synced', 'tally_voucher_id' => '1']);
    }

    public function test_invoice_creation_still_succeeds_when_tally_is_unreachable(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        TallyConnection::create([
            'host' => '192.168.1.50', 'port' => 9000, 'company_name' => 'Oracle Machine Tech',
            'username' => 'admin', 'password' => 'secret', 'status' => 'connected',
        ]);

        Http::fake(function () {
            throw new ConnectionException('timed out');
        });

        $response = $this->postInvoice($user, $product);

        $response->assertOk();
        $this->assertDatabaseHas('tally_sync_queue', ['status' => 'failed']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallyInvoicePostingIntegrationTest`
Expected: FAIL — `TallyPostingService` not wired in yet, so every row stays `pending` regardless of connection state.

- [ ] **Step 3: Add the import and constructor dependency**

In `app/Services/Invoice/InvoiceService.php`, add the import near the top:

```php
use App\Services\Integration\TallyPostingService;
```

Change the constructor:

```php
    public function __construct(
        private InvoiceRepositoryInterface $repository,
        private ProductRepositoryInterface $productRepository,
        private BankRepositoryInterface $bankRepository,
        private TallySyncQueueService $tallySyncQueueService,
        private TallyPostingService $tallyPostingService,
    ) {
    }
```

- [ ] **Step 4: Return the queue row from the transaction, then attempt posting outside it**

`createInvoiceWithDetails()` currently wraps its whole body in `DB::transaction(function () use ($request) { ... $this->tallySyncQueueService->enqueueSalesInvoice($id); });` with no return value. Change the last line inside the closure from:

```php
            $this->tallySyncQueueService->enqueueSalesInvoice($id);
        });
    }
```

to:

```php
            return $this->tallySyncQueueService->enqueueSalesInvoice($id);
        });

        // Attempted outside the transaction - this makes a real network
        // call to Tally, which must never hold the invoice's DB
        // transaction open. A Tally failure here must never break invoice
        // creation, which has already committed by this point.
        try {
            $this->tallyPostingService->attempt($queue);
        } catch (\Throwable $e) {
            report($e);
        }
    }
```

And change the method's opening line from:

```php
    public function createInvoiceWithDetails(Request $request): void
    {
        DB::transaction(function () use ($request) {
```

to:

```php
    public function createInvoiceWithDetails(Request $request): void
    {
        $queue = DB::transaction(function () use ($request) {
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=TallyInvoicePostingIntegrationTest`
Expected: PASS (3 tests)

- [ ] **Step 6: Run the existing invoice + Tally test suites to confirm nothing broke**

Run: `php artisan test --filter=InvoiceTest`
Run: `php artisan test --filter=TallySyncQueueTest`
Expected: PASS (all existing tests, unchanged)

- [ ] **Step 7: Commit**

```bash
git add app/Services/Invoice/InvoiceService.php tests/Feature/TallyInvoicePostingIntegrationTest.php
git commit -m "$(cat <<'EOF'
feat: attempt posting to Tally immediately after invoice creation

The queue row is created inside the existing DB transaction as
before; TallyPostingService::attempt() runs right after it commits
(never inside it, since it makes a real network call) and any
exception is caught and reported, never allowed to break invoice
creation.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 8: Scheduled retry sweep

**Files:**
- Create: `app/Console/Commands/TallySyncPending.php`
- Modify: `app/Console/Kernel.php`
- Test: `tests/Feature/TallySyncPendingCommandTest.php`

**Interfaces:**
- Consumes: `App\Services\Integration\TallyPostingService::attempt()` (Task 6), `App\Models\TallySyncQueue`.
- Produces: artisan command `tally:sync-pending`, registered on Laravel's scheduler.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\TallyConnection;
use App\Models\TallySyncQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TallySyncPendingCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_retries_pending_rows_and_skips_over_cap_failed_rows(): void
    {
        TallyConnection::create([
            'host' => '192.168.1.50', 'port' => 9000, 'company_name' => 'Oracle Machine Tech',
            'username' => 'admin', 'password' => 'secret', 'status' => 'connected',
        ]);

        $pending = TallySyncQueue::create([
            'source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'A',
            'payload' => [
                'invoice_id' => 1, 'invoice_number' => 'INV-1', 'invoice_date' => '2026-09-22',
                'customer' => ['name' => 'Test'], 'total_before_tax' => 100.0, 'total_with_tax' => 118.0,
                'sgst_amount' => 9.0, 'cgst_amount' => 9.0, 'igst_amount' => 0.0, 'line_items' => [],
            ],
            'status' => 'pending',
        ]);
        $overCap = TallySyncQueue::create([
            'source_type' => 'x', 'source_id' => 2, 'voucher_type' => 'sales', 'reference_no' => 'B',
            'payload' => [], 'status' => 'failed', 'attempts' => 5,
        ]);

        Http::fake([
            'http://192.168.1.50:9000' => Http::response('<RESPONSE><CREATED>1</CREATED><ERRORS>0</ERRORS><LASTVCHID>1</LASTVCHID></RESPONSE>', 200),
        ]);

        $this->artisan('tally:sync-pending')->assertExitCode(0);

        $pending->refresh();
        $overCap->refresh();

        $this->assertSame('synced', $pending->status);
        $this->assertSame('failed', $overCap->status);
        $this->assertSame(5, $overCap->attempts);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallySyncPendingCommandTest`
Expected: FAIL — command `tally:sync-pending` doesn't exist yet.

- [ ] **Step 3: Write the command**

```php
<?php

namespace App\Console\Commands;

use App\Models\TallySyncQueue;
use App\Services\Integration\TallyPostingService;
use Illuminate\Console\Command;

class TallySyncPending extends Command
{
    protected $signature = 'tally:sync-pending';

    protected $description = 'Retry posting pending and under-cap failed tally_sync_queue rows to Tally.';

    public function handle(TallyPostingService $service): int
    {
        $rows = TallySyncQueue::whereIn('status', ['pending', 'failed'])->get();

        foreach ($rows as $row) {
            $service->attempt($row);
        }

        $this->info("Processed {$rows->count()} Tally sync queue row(s).");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Register it on the scheduler**

In `app/Console/Kernel.php`, change:

```php
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('jobs:flag-overdue')->dailyAt('07:00');
        $schedule->command('inventory:flag-low-stock')->dailyAt('07:15');
    }
```

to:

```php
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('jobs:flag-overdue')->dailyAt('07:00');
        $schedule->command('inventory:flag-low-stock')->dailyAt('07:15');
        $schedule->command('tally:sync-pending')->everyFiveMinutes();
    }
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=TallySyncPendingCommandTest`
Expected: PASS (1 test)

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/TallySyncPending.php app/Console/Kernel.php tests/Feature/TallySyncPendingCommandTest.php
git commit -m "$(cat <<'EOF'
feat: add scheduled retry sweep for pending/failed Tally sync rows

Runs every 5 minutes via the existing cron-driven scheduler (no new
queue-worker infrastructure) - reuses TallyPostingService::attempt(),
which already skips synced rows and failed rows over the attempt cap.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 9: Replace the status screen's agent-staleness warning with a connection-status warning

**Files:**
- Modify: `app/Http/Controllers/Integration/TallySyncController.php`
- Modify: `resources/views/integration/tally-sync/index.blade.php`
- Modify: `tests/Feature/TallySyncStatusScreenTest.php`

**Interfaces:**
- Consumes: `App\Models\TallyConnection` (Task 1).

- [ ] **Step 1: Update the two staleness tests to the new behavior**

In `tests/Feature/TallySyncStatusScreenTest.php`, replace these two test methods:

```php
    public function test_status_screen_warns_when_the_agent_has_never_checked_in(): void
    {
        $owner = $this->owner();
        \Illuminate\Support\Facades\Cache::forget('tally_agent_last_checkin');

        $this->actingAs($owner)->get(route('tally-sync.index'))->assertSee('Agent has not checked in');
    }

    public function test_status_screen_does_not_warn_when_the_agent_checked_in_recently(): void
    {
        $owner = $this->owner();
        \Illuminate\Support\Facades\Cache::put('tally_agent_last_checkin', now(), now()->addDay());

        $this->actingAs($owner)->get(route('tally-sync.index'))->assertDontSee('Agent has not checked in');
    }
```

with:

```php
    public function test_status_screen_warns_when_tally_is_not_connected(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->get(route('tally-sync.index'))->assertSee('Tally is not connected');
    }

    public function test_status_screen_does_not_warn_when_tally_is_connected(): void
    {
        $owner = $this->owner();
        \App\Models\TallyConnection::create([
            'host' => '192.168.1.50', 'port' => 9000, 'company_name' => 'Oracle Machine Tech',
            'username' => 'admin', 'password' => 'secret', 'status' => 'connected',
        ]);

        $this->actingAs($owner)->get(route('tally-sync.index'))->assertDontSee('Tally is not connected');
    }
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=TallySyncStatusScreenTest`
Expected: FAIL — the view still renders the old "Agent has not checked in" text regardless of connection state.

- [ ] **Step 3: Update the controller**

Replace `app/Http/Controllers/Integration/TallySyncController.php`'s full contents with:

```php
<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\TallyConnection;
use App\Models\TallySyncQueue;

class TallySyncController extends Controller
{
    public function index()
    {
        $connection = TallyConnection::first();

        return view('integration.tally-sync.index', [
            'queue' => TallySyncQueue::orderByDesc('id')->paginate(25),
            'notConnected' => $connection === null || $connection->status !== 'connected',
        ]);
    }

    public function retry(TallySyncQueue $tallySyncQueue)
    {
        $tallySyncQueue->update(['status' => 'pending', 'last_error' => null]);

        return redirect()->route('tally-sync.index')->with('success', 'Queued for retry.');
    }
}
```

- [ ] **Step 4: Update the view**

In `resources/views/integration/tally-sync/index.blade.php`, replace:

```blade
    <p class="text-muted small mb-3">Invoices queued for the local Tally sync agent to post into Tally Prime as Sales vouchers. Failed rows can be retried once the underlying issue (e.g. a missing ledger in Tally) is fixed.</p>

    @if ($agentIsStale)
    <div class="alert alert-warning">
        <i class="ri-error-warning-line align-middle me-1"></i>
        @if ($lastCheckin)
            Agent has not checked in since {{ $lastCheckin->diffForHumans() }} &mdash; confirm it's still running on the Tally machine.
        @else
            Agent has not checked in yet &mdash; confirm it's installed and running on the Tally machine.
        @endif
    </div>
    @endif
```

with:

```blade
    <p class="text-muted small mb-3">Invoices queued to post into Tally Prime as Sales vouchers. Failed rows can be retried once the underlying issue (e.g. a missing ledger in Tally) is fixed.</p>

    @if ($notConnected)
    <div class="alert alert-warning">
        <i class="ri-error-warning-line align-middle me-1"></i>
        Tally is not connected &mdash; <a href="{{ route('tally-connection.edit') }}">check the connection settings</a> before invoices can sync.
    </div>
    @endif
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --filter=TallySyncStatusScreenTest`
Expected: PASS (5 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Integration/TallySyncController.php resources/views/integration/tally-sync/index.blade.php tests/Feature/TallySyncStatusScreenTest.php
git commit -m "$(cat <<'EOF'
refactor: replace agent-staleness warning with connection-status warning

There is no polling agent anymore (direct connection replaces it), so
tally_agent_last_checkin no longer means anything - the status screen
now warns based on tally_connections.status instead, linking to the
new connection settings screen.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 10: Full verification pass

**Files:** none (verification only)

- [ ] **Step 1: Run the full test suite**

Run: `php artisan test`
Expected: PASS — all tests green, including every new/changed test file from Tasks 1-9 and the full pre-existing suite.

- [ ] **Step 2: Migrate against live MySQL**

Run: `php artisan migrate` (against the real `u411614341_Invoice` database, per `deployment_operational_notes` conventions used elsewhere in this project)
Expected: `tally_connections` table created with no errors. No `RolesAndPermissionsSeeder` re-run is needed — this plan reuses the existing `tally-sync.view` permission, no new permission was added.

- [ ] **Step 3: Set the ledger-name env vars in production**

Add to the production `.env` (values must match the accountant's actual Tally chart of accounts — confirm with them, the defaults are placeholders):

```
TALLY_SALES_LEDGER="Sales Account"
TALLY_CGST_LEDGER=CGST
TALLY_SGST_LEDGER=SGST
TALLY_IGST_LEDGER=IGST
```

- [ ] **Step 4: Manually verify against the real Tally server**

This is the step the automated suite cannot cover (see Global Constraints above and the design spec's "Open questions" section) — the exact XML tags for Security-Control auth and the Sales-voucher envelope are unconfirmed until tried against the real server:

- Log in as an Owner user, visit `/tally-connection`, enter the real server's host/port/company name/username/password, save, click Connect.
- If "Connection failed" appears: read `last_error` — it will usually say either a network problem (host/port wrong, firewall) or "did not return company X" (the `SVCURRENTUSER`/`SVCURRENTUSERPASSWORD` tags in `TallyConnectionService::buildListCompaniesRequest()` are the most likely thing to need adjusting for this Tally version — check Tally's own XML API documentation or contact Tally support for the exact security tag names if this fails).
- Once connected, create one real test invoice through the UI and confirm a `tally_sync_queue` row moves to `synced` (or read its `last_error` if `failed` — most likely a missing ledger/stock item in Tally, matching the "fail, don't auto-create" policy; create the missing master in Tally and use the status screen's Retry button).
- Verify the resulting voucher actually appears correctly in Tally (right customer, amounts, tax split, and stock item quantities) — if the debit/credit signs look backwards, flip the `true`/`false` `ISDEEMEDPOSITIVE` arguments in `TallyVoucherXmlBuilder::buildVoucher()`'s two `ledgerEntry()` calls (customer vs. sales/tax) and re-test.

- [ ] **Step 5: Report results**

If Step 4 passes end-to-end, this plan is complete. Any XML tag adjustments made during Step 4 should be committed as a small follow-up fix (with a note of what was wrong and what real Tally expects, for the next voucher type's implementation).
