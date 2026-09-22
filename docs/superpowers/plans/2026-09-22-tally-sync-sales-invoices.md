# Tally Sync — Sales Invoices (Stage 1, CRM side) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the CRM-side half of the Tally integration's Sales-invoice pilot — a queue that captures every new invoice as a pending Tally Sales voucher, an API the (separately-built) local sync agent authenticates against and polls, and a status screen so Owner/Account can see and retry sync activity.

**Architecture:** A new `tally_sync_queue` table records one row per invoice that needs to reach Tally. `InvoiceService::createInvoiceWithDetails()` gets one additional call appended at the end (existing logic untouched) that snapshots the invoice into a queue row via a new `TallySyncQueueService`. Two Sanctum-authenticated API endpoints (`GET /api/tally-sync/pending`, `POST /api/tally-sync/{id}/acknowledge`) let the agent (built in a separate follow-up plan, against a real Tally Prime test company) pull pending rows and report results. A permission-gated web screen lists queue status with a retry action.

**Tech Stack:** Laravel 10, Spatie Permission (`permission` middleware), Laravel Sanctum (token abilities), MySQL (live) / SQLite (tests), Blade + existing `x-ui` component library.

## Scope note

This plan covers the CRM side only, for the Sales-invoice voucher type — per
`docs/superpowers/specs/2026-09-22-tally-integration-design.md`'s explicit
rollout order (sales first, as the pilot that proves the pipeline end to
end). It does **not** build the sync agent itself (a separate application,
tested against a real Tally Prime instance — genuinely different tech stack
and verification posture, gets its own plan once this one is deployed and
there's a live API for it to poll). It does **not** cover vendor
bills/purchases, daily expenses, or payroll — those are separate plans once
this pilot is validated, per the spec.

## Global Constraints

- One-way sync only: CRM → Tally. Nothing in this plan reads from Tally.
- Tally's own XML gateway is never touched by the CRM directly — the agent
  (out of scope here) owns that boundary.
- Follow this codebase's existing conventions exactly: `permission:` Blade
  `@can`/middleware for web UI (Spatie Permission), Sanctum ability
  middleware for the agent's API, `RefreshDatabase` + seeded
  `RolesAndPermissionsSeeder` in tests, `syncRoles()` (not `assignRole()`)
  whenever a test needs a user isolated to exactly one non-Owner role.
- No changes to `InvoiceService::createInvoiceWithDetails()`'s existing
  behavior beyond appending one call — its existing tests must keep passing
  unmodified.

---

### Task 1: `tally_sync_queue` table + model

**Files:**
- Create: `database/migrations/2026_09_22_000000_create_tally_sync_queue_table.php`
- Create: `app/Models/TallySyncQueue.php`
- Test: `tests/Feature/TallySyncQueueModelTest.php`

**Interfaces:**
- Produces: `App\Models\TallySyncQueue` — Eloquent model, table `tally_sync_queue`, fillable `source_type, source_id, voucher_type, reference_no, payload, status, tally_voucher_id, attempts, last_error`, `payload` cast to `array`, query scopes `pending()` and `failed()` (both filter on `status`).

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\TallySyncQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallySyncQueueModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_and_casts_the_payload_as_an_array(): void
    {
        $queue = TallySyncQueue::create([
            'source_type' => 'App\\Models\\Invoice',
            'source_id' => 42,
            'voucher_type' => 'sales',
            'reference_no' => 'SALES-42',
            'payload' => ['invoice_number' => 'INV-001', 'total_with_tax' => 59000.0],
            'status' => 'pending',
        ]);

        $fresh = TallySyncQueue::find($queue->id);

        $this->assertSame('pending', $fresh->status);
        $this->assertIsArray($fresh->payload);
        $this->assertSame('INV-001', $fresh->payload['invoice_number']);
    }

    public function test_pending_and_failed_scopes_filter_by_status(): void
    {
        TallySyncQueue::create(['source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'A', 'payload' => [], 'status' => 'pending']);
        TallySyncQueue::create(['source_type' => 'x', 'source_id' => 2, 'voucher_type' => 'sales', 'reference_no' => 'B', 'payload' => [], 'status' => 'failed']);
        TallySyncQueue::create(['source_type' => 'x', 'source_id' => 3, 'voucher_type' => 'sales', 'reference_no' => 'C', 'payload' => [], 'status' => 'synced']);

        $this->assertSame(['A'], TallySyncQueue::pending()->pluck('reference_no')->all());
        $this->assertSame(['B'], TallySyncQueue::failed()->pluck('reference_no')->all());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallySyncQueueModelTest`
Expected: FAIL — table `tally_sync_queue` / class `TallySyncQueue` don't exist yet.

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
        // No foreign key on source_id: source_type varies across Invoice /
        // VendorBill / SalaryPayment / DailyTransaction (future stages),
        // whose id columns aren't all the same underlying type (see the
        // product/invoice.id signed-int gotcha) - polymorphic reference by
        // design, not enforced at the DB level.
        Schema::create('tally_sync_queue', function (Blueprint $table) {
            $table->id();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->string('voucher_type');
            $table->string('reference_no')->unique();
            $table->json('payload');
            $table->enum('status', ['pending', 'synced', 'failed'])->default('pending');
            $table->string('tally_voucher_id')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tally_sync_queue');
    }
};
```

- [ ] **Step 4: Write the model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TallySyncQueue extends Model
{
    protected $table = 'tally_sync_queue';

    protected $fillable = [
        'source_type',
        'source_id',
        'voucher_type',
        'reference_no',
        'payload',
        'status',
        'tally_voucher_id',
        'attempts',
        'last_error',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=TallySyncQueueModelTest`
Expected: PASS (2 tests)

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_09_22_000000_create_tally_sync_queue_table.php app/Models/TallySyncQueue.php tests/Feature/TallySyncQueueModelTest.php
git commit -m "$(cat <<'EOF'
feat: add tally_sync_queue table and model

First piece of the Tally Sales-invoice sync pilot - a queue that will
hold one row per invoice pending delivery to Tally Prime via the
(separately built) local sync agent.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 2: Company config + `TallySyncQueueService::enqueueSalesInvoice()`

**Files:**
- Create: `config/tally.php`
- Modify: `.env.example` (add 3 keys)
- Create: `app/Services/Integration/TallySyncQueueService.php`
- Test: `tests/Feature/TallySyncQueueServiceTest.php`

**Interfaces:**
- Consumes: `App\Models\TallySyncQueue` (Task 1), `App\Models\Invoice` (has `customer(): HasOne`), `App\Models\Invoiceproduct`, `App\Support\IndianStates::HOME_STATE`.
- Produces: `App\Services\Integration\TallySyncQueueService::enqueueSalesInvoice(int $invoiceId): TallySyncQueue` — builds a payload snapshot and creates a `tally_sync_queue` row with `voucher_type = 'sales'`, `reference_no = 'SALES-{invoiceId}'`, `status = 'pending'`. Task 3 calls this.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\TallySyncQueue;
use App\Services\Integration\TallySyncQueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallySyncQueueServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_enqueues_a_sales_voucher_with_gujarat_customer_as_sgst_cgst_split(): void
    {
        config(['tally.company.name' => 'Oracle Machine Tech', 'tally.company.gstin' => '24AAAAA0000A1Z5', 'tally.company.state' => 'Gujarat']);

        $invoice = Invoice::create([
            'invoice_id' => 'INV-001',
            'date' => '2026-09-22',
            'totalamountbeforetax' => 50000,
            'amountwithtax' => 59000,
        ]);
        Customer::create([
            'invoice_id' => $invoice->id,
            'name' => 'Test Customer',
            'address' => '123 Main St',
            'state' => 'Gujarat',
            'billinggst' => '24BBBBB1111B1Z5',
        ]);
        Invoiceproduct::create([
            'invoice_id' => $invoice->id,
            'product_name' => 'Widget',
            'hsn' => '8456',
            'unit' => 'Nos',
            'rate' => 50000,
            'quantity' => 1,
            'total' => 50000,
            'gst' => 18,
            'gstamount' => 9000,
            'totalamount' => 59000,
        ]);

        $queue = (new TallySyncQueueService())->enqueueSalesInvoice($invoice->id);

        $this->assertSame('sales', $queue->voucher_type);
        $this->assertSame('pending', $queue->status);
        $this->assertSame('SALES-'.$invoice->id, $queue->reference_no);
        $this->assertSame('App\\Models\\Invoice', $queue->source_type);
        $this->assertSame($invoice->id, $queue->source_id);
        $this->assertSame('Test Customer', $queue->payload['customer']['name']);
        $this->assertSame('Oracle Machine Tech', $queue->payload['company']['name']);
        $this->assertSame(4500.0, $queue->payload['sgst_amount']);
        $this->assertSame(4500.0, $queue->payload['cgst_amount']);
        $this->assertSame(0.0, $queue->payload['igst_amount']);
        $this->assertCount(1, $queue->payload['line_items']);
        $this->assertSame('8456', $queue->payload['line_items'][0]['hsn']);

        $this->assertDatabaseHas('tally_sync_queue', ['reference_no' => 'SALES-'.$invoice->id]);
    }

    public function test_it_splits_out_of_state_customer_as_igst_only(): void
    {
        $invoice = Invoice::create(['invoice_id' => 'INV-002', 'date' => '2026-09-22', 'totalamountbeforetax' => 1000, 'amountwithtax' => 1180]);
        Customer::create(['invoice_id' => $invoice->id, 'name' => 'Out Of State', 'state' => 'Maharashtra']);
        Invoiceproduct::create(['invoice_id' => $invoice->id, 'product_name' => 'Widget', 'gst' => 18, 'gstamount' => 180, 'totalamount' => 1180]);

        $queue = (new TallySyncQueueService())->enqueueSalesInvoice($invoice->id);

        $this->assertSame(0.0, $queue->payload['sgst_amount']);
        $this->assertSame(0.0, $queue->payload['cgst_amount']);
        $this->assertSame(180.0, $queue->payload['igst_amount']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallySyncQueueServiceTest`
Expected: FAIL — class `TallySyncQueueService` doesn't exist yet.

- [ ] **Step 3: Write the config file**

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
];
```

- [ ] **Step 4: Add the env keys**

Add to `.env.example` (new lines, end of file):

```
TALLY_COMPANY_NAME="Oracle Machine Tech"
TALLY_COMPANY_GSTIN=
TALLY_COMPANY_STATE=Gujarat
```

- [ ] **Step 5: Write the service**

```php
<?php

namespace App\Services\Integration;

use App\Models\Invoice;
use App\Models\Invoiceproduct;
use App\Models\TallySyncQueue;
use App\Support\IndianStates;

class TallySyncQueueService
{
    /** Snapshot an invoice into a pending Tally Sales-voucher queue row. */
    public function enqueueSalesInvoice(int $invoiceId): TallySyncQueue
    {
        $invoice = Invoice::with('customer')->findOrFail($invoiceId);
        $customer = $invoice->customer;
        $lineItems = Invoiceproduct::where('invoice_id', $invoiceId)->get();

        $totalGst = (float) $lineItems->sum('gstamount');
        $isHomeState = $customer?->state === IndianStates::HOME_STATE;

        $payload = [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_id,
            'invoice_date' => $invoice->date,
            'company' => [
                'name' => config('tally.company.name'),
                'gstin' => config('tally.company.gstin'),
                'state' => config('tally.company.state'),
            ],
            'customer' => [
                'name' => $customer?->name,
                'address' => $customer?->address,
                'state' => $customer?->state,
                'gstin' => $customer?->billinggst,
                'pan' => $customer?->billingpan,
            ],
            'total_before_tax' => (float) $invoice->totalamountbeforetax,
            'total_with_tax' => (float) $invoice->amountwithtax,
            'sgst_amount' => $isHomeState ? round($totalGst / 2, 2) : 0.0,
            'cgst_amount' => $isHomeState ? round($totalGst / 2, 2) : 0.0,
            'igst_amount' => $isHomeState ? 0.0 : round($totalGst, 2),
            'line_items' => $lineItems->map(fn (Invoiceproduct $line) => [
                'product_name' => $line->product_name,
                'hsn' => $line->hsn,
                'unit' => $line->unit,
                'quantity' => $line->quantity,
                'rate' => $line->rate,
                'gst_percent' => $line->gst,
                'gst_amount' => $line->gstamount,
                'total_amount' => $line->totalamount,
            ])->values()->all(),
        ];

        return TallySyncQueue::create([
            'source_type' => Invoice::class,
            'source_id' => $invoice->id,
            'voucher_type' => 'sales',
            'reference_no' => 'SALES-'.$invoice->id,
            'payload' => $payload,
            'status' => 'pending',
        ]);
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=TallySyncQueueServiceTest`
Expected: PASS (2 tests)

- [ ] **Step 7: Commit**

```bash
git add config/tally.php .env.example app/Services/Integration/TallySyncQueueService.php tests/Feature/TallySyncQueueServiceTest.php
git commit -m "$(cat <<'EOF'
feat: add TallySyncQueueService to snapshot invoices as Sales-voucher payloads

Builds the queue row's payload from the invoice, customer and line
items, reusing the same home-state SGST/CGST vs. IGST split rule as
InvoiceService::getInvoiceDetails(). Not yet wired into invoice
creation - that's the next task.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 3: Hook into `InvoiceService::createInvoiceWithDetails()`

**Files:**
- Modify: `app/Services/Invoice/InvoiceService.php`
- Test: `tests/Feature/TallySyncQueueTest.php`

**Interfaces:**
- Consumes: `App\Services\Integration\TallySyncQueueService::enqueueSalesInvoice(int $invoiceId): TallySyncQueue` (Task 2).

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallySyncQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_invoice_enqueues_a_pending_tally_sales_voucher(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(route('invoice.store'), [
            'invoice_id' => 'INV-TALLY-001',
            'invoice_date' => '2026-09-22',
            'placesupply' => 'Gujarat',
            'billing_address_full_name' => 'Tally Test Customer',
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

        $response->assertOk();

        $invoiceId = \App\Models\Invoice::where('invoice_id', 'INV-TALLY-001')->firstOrFail()->id;

        $this->assertDatabaseHas('tally_sync_queue', [
            'reference_no' => 'SALES-'.$invoiceId,
            'voucher_type' => 'sales',
            'status' => 'pending',
        ]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallySyncQueueTest`
Expected: FAIL — no `tally_sync_queue` row created yet.

- [ ] **Step 3: Add the hook**

In `app/Services/Invoice/InvoiceService.php`, add the import near the top:

```php
use App\Services\Integration\TallySyncQueueService;
```

Change the constructor:

```php
    public function __construct(
        private InvoiceRepositoryInterface $repository,
        private ProductRepositoryInterface $productRepository,
        private BankRepositoryInterface $bankRepository,
        private TallySyncQueueService $tallySyncQueueService,
    ) {
    }
```

In `createInvoiceWithDetails()`, the transaction closure currently ends like this (inside the `foreach ($products as $productLine)` loop, closing out the method):

```php
                        'gstamount' => $productLine['withtax'] ?? null,
                        'totalamount' => $productLine['total'] ?? null,
                    ]);
                }
            }
        });
    }
```

Change it to enqueue the Tally sync row as the last thing inside the transaction, after the products loop:

```php
                        'gstamount' => $productLine['withtax'] ?? null,
                        'totalamount' => $productLine['total'] ?? null,
                    ]);
                }
            }

            $this->tallySyncQueueService->enqueueSalesInvoice($id);
        });
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=TallySyncQueueTest`
Expected: PASS

- [ ] **Step 5: Run the existing invoice test suite to confirm nothing broke**

Run: `php artisan test --filter=InvoiceTest`
Expected: PASS (all existing tests, unchanged)

- [ ] **Step 6: Commit**

```bash
git add app/Services/Invoice/InvoiceService.php tests/Feature/TallySyncQueueTest.php
git commit -m "$(cat <<'EOF'
feat: enqueue a Tally Sales-voucher sync row on invoice creation

Additive hook at the end of InvoiceService::createInvoiceWithDetails()'s
existing transaction - every new invoice now queues a pending Tally
sync row automatically, no change to the method's existing behavior.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 4: Agent authentication (Sanctum ability) + `tally-sync.view` permission

**Files:**
- Modify: `app/Http/Kernel.php`
- Modify: `database/seeders/RolesAndPermissionsSeeder.php`
- Create: `app/Console/Commands/CreateTallyAgentToken.php`
- Test: `tests/Feature/CreateTallyAgentTokenTest.php`

**Interfaces:**
- Produces: `ability`/`abilities` route middleware aliases (Sanctum), permission `tally-sync.view` (Owner + Account roles), artisan command `tally:create-agent-token {user_email}` that issues a token with the `tally-agent` ability.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTallyAgentTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_issues_a_token_scoped_to_the_tally_agent_ability(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);

        $this->artisan('tally:create-agent-token', ['user_email' => 'owner@example.com'])
            ->assertExitCode(0);

        $token = $user->tokens()->first();

        $this->assertNotNull($token);
        $this->assertSame(['tally-agent'], $token->abilities);
    }

    public function test_it_fails_cleanly_for_an_unknown_email(): void
    {
        $this->artisan('tally:create-agent-token', ['user_email' => 'nobody@example.com'])
            ->assertExitCode(1);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CreateTallyAgentTokenTest`
Expected: FAIL — command doesn't exist yet.

- [ ] **Step 3: Register the Sanctum ability middleware**

In `app/Http/Kernel.php`, add to `$routeMiddleware` (Sanctum ships these classes but doesn't auto-register the aliases in this Laravel version):

```php
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
```

(inserted alphabetically between the existing `permission` and `signed` entries)

- [ ] **Step 4: Add the `tally-sync.view` permission**

In `database/seeders/RolesAndPermissionsSeeder.php`, add to the `PERMISSIONS` array (after the `accounting.view` line):

```php
        'accounting.view',
        'tally-sync.view',
```

Add it to the `Account` role's `syncPermissions` call too:

```php
        $account->syncPermissions([
            'dashboard.view',
            'invoices.view', 'invoices.view-details', 'invoices.record-payment',
            'payment-history.view',
            'expenses.view', 'expenses.manage',
            'vendors.view', 'vendor-payments.view', 'vendor-payments.manage',
            'reports.view', 'accounting.view', 'tally-sync.view',
        ]);
```

(`Owner` already gets every permission in `PERMISSIONS` via `$owner->syncPermissions(self::PERMISSIONS)`, no separate change needed there.)

- [ ] **Step 5: Write the console command**

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateTallyAgentToken extends Command
{
    protected $signature = 'tally:create-agent-token {user_email}';

    protected $description = 'Issue a Sanctum API token scoped to the tally-agent ability, for the local Tally sync agent to authenticate with.';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('user_email'))->first();

        if (! $user) {
            $this->error('No user found with that email.');

            return self::FAILURE;
        }

        $token = $user->createToken('tally-sync-agent', ['tally-agent']);

        $this->info('Agent token (copy this into the agent config, it will not be shown again):');
        $this->line($token->plainTextToken);

        return self::SUCCESS;
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=CreateTallyAgentTokenTest`
Expected: PASS (2 tests)

- [ ] **Step 7: Commit**

```bash
git add app/Http/Kernel.php database/seeders/RolesAndPermissionsSeeder.php app/Console/Commands/CreateTallyAgentToken.php tests/Feature/CreateTallyAgentTokenTest.php
git commit -m "$(cat <<'EOF'
feat: add Tally agent token issuance and tally-sync.view permission

Registers Sanctum's ability/abilities middleware aliases (not
auto-registered in this Laravel version) and a console command to
issue a tally-agent-scoped token for the local sync agent to
authenticate with the CRM's API. Adds tally-sync.view for Owner +
Account, gating the status screen built in Task 6.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 5: Agent-facing API (`pending` / `acknowledge`)

**Files:**
- Create: `app/Http/Controllers/Api/TallySyncApiController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/TallySyncApiTest.php`

**Interfaces:**
- Consumes: `App\Models\TallySyncQueue` (Task 1), `ability`/`abilities` middleware (Task 4).
- Produces: `GET /api/tally-sync/pending` (named `api.tally-sync.pending`) → `{"data": [...]}` of pending queue rows, and records a `tally_agent_last_checkin` cache timestamp (read by Task 6's status screen to show a stale-agent warning, per the spec's Error handling & visibility section). `POST /api/tally-sync/{tallySyncQueue}/acknowledge` (named `api.tally-sync.acknowledge`) → accepts `{status, tally_voucher_id?, error?}`, updates the row, returns `{"isSuccess": true}`. Both require a Sanctum token with the `tally-agent` ability.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\TallySyncQueue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TallySyncApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_token_can_list_pending_rows_only(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['tally-agent']);

        TallySyncQueue::create(['source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'A', 'payload' => [], 'status' => 'pending']);
        TallySyncQueue::create(['source_type' => 'x', 'source_id' => 2, 'voucher_type' => 'sales', 'reference_no' => 'B', 'payload' => [], 'status' => 'synced']);

        $response = $this->getJson('/api/tally-sync/pending');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.reference_no', 'A');
    }

    public function test_a_token_without_the_tally_agent_ability_is_rejected(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['some-other-ability']);

        $this->getJson('/api/tally-sync/pending')->assertForbidden();
    }

    public function test_an_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/tally-sync/pending')->assertUnauthorized();
    }

    public function test_a_pending_poll_records_the_agent_checkin_time(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['tally-agent']);

        \Illuminate\Support\Facades\Cache::forget('tally_agent_last_checkin');
        $this->getJson('/api/tally-sync/pending')->assertOk();

        $this->assertNotNull(\Illuminate\Support\Facades\Cache::get('tally_agent_last_checkin'));
    }

    public function test_agent_can_acknowledge_a_successful_sync(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['tally-agent']);

        $queue = TallySyncQueue::create(['source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'A', 'payload' => [], 'status' => 'pending']);

        $response = $this->postJson("/api/tally-sync/{$queue->id}/acknowledge", [
            'status' => 'synced',
            'tally_voucher_id' => 'TALLY-VCH-99',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tally_sync_queue', [
            'id' => $queue->id,
            'status' => 'synced',
            'tally_voucher_id' => 'TALLY-VCH-99',
            'attempts' => 1,
        ]);
    }

    public function test_agent_can_acknowledge_a_failed_sync_with_an_error(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['tally-agent']);

        $queue = TallySyncQueue::create(['source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'A', 'payload' => [], 'status' => 'pending']);

        $this->postJson("/api/tally-sync/{$queue->id}/acknowledge", [
            'status' => 'failed',
            'error' => 'Ledger not found in Tally',
        ])->assertOk();

        $this->assertDatabaseHas('tally_sync_queue', [
            'id' => $queue->id,
            'status' => 'failed',
            'last_error' => 'Ledger not found in Tally',
            'attempts' => 1,
        ]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallySyncApiTest`
Expected: FAIL — route/controller don't exist yet.

- [ ] **Step 3: Write the controller**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TallySyncQueue;
use Illuminate\Http\Request;

class TallySyncApiController extends Controller
{
    public function pending()
    {
        // Read by the status screen (Task 6) to warn when the agent hasn't
        // polled recently - the only signal the CRM has that it's still
        // running, since it never receives an inbound connection from it.
        cache()->put('tally_agent_last_checkin', now(), now()->addDay());

        return response()->json([
            'data' => TallySyncQueue::pending()->orderBy('id')->get(),
        ]);
    }

    public function acknowledge(Request $request, TallySyncQueue $tallySyncQueue)
    {
        $data = $request->validate([
            'status' => 'required|in:synced,failed',
            'tally_voucher_id' => 'nullable|string|max:255',
            'error' => 'nullable|string|max:2000',
        ]);

        $tallySyncQueue->update([
            'status' => $data['status'],
            'tally_voucher_id' => $data['tally_voucher_id'] ?? null,
            'last_error' => $data['status'] === 'failed' ? ($data['error'] ?? null) : null,
            'attempts' => $tallySyncQueue->attempts + 1,
        ]);

        return response()->json(['isSuccess' => true]);
    }
}
```

- [ ] **Step 4: Add the routes**

In `routes/api.php`, add after the existing `/user` route:

```php
Route::middleware(['auth:sanctum', 'abilities:tally-agent'])->prefix('tally-sync')->group(function () {
    Route::get('pending', [App\Http\Controllers\Api\TallySyncApiController::class, 'pending'])->name('api.tally-sync.pending');
    Route::post('{tallySyncQueue}/acknowledge', [App\Http\Controllers\Api\TallySyncApiController::class, 'acknowledge'])->name('api.tally-sync.acknowledge');
});
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=TallySyncApiTest`
Expected: PASS (6 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/TallySyncApiController.php routes/api.php tests/Feature/TallySyncApiTest.php
git commit -m "$(cat <<'EOF'
feat: add agent-facing Tally sync API (pending / acknowledge)

The local sync agent (built separately, against a real Tally Prime
test company) authenticates with a tally-agent-scoped Sanctum token
and polls GET /api/tally-sync/pending, then reports each voucher's
outcome via POST /api/tally-sync/{id}/acknowledge.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 6: "Tally Sync" status screen (Owner/Account)

**Files:**
- Create: `app/Http/Controllers/Integration/TallySyncController.php`
- Create: `resources/views/integration/tally-sync/index.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/sidebar.blade.php`
- Test: `tests/Feature/TallySyncStatusScreenTest.php`

**Interfaces:**
- Consumes: `App\Models\TallySyncQueue` (Task 1), `tally-sync.view` permission (Task 4), `tally_agent_last_checkin` cache key (Task 5).
- Produces: `GET /tally-sync` (named `tally-sync.index`), `POST /tally-sync/{tallySyncQueue}/retry` (named `tally-sync.retry`) — both permission-gated.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\TallySyncQueue;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallySyncStatusScreenTest extends TestCase
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

    public function test_owner_can_view_the_status_screen(): void
    {
        $owner = $this->owner();

        TallySyncQueue::create(['source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'SALES-1', 'payload' => [], 'status' => 'failed', 'last_error' => 'Ledger not found']);

        $response = $this->actingAs($owner)->get(route('tally-sync.index'));

        $response->assertOk();
        $response->assertSee('SALES-1');
        $response->assertSee('Ledger not found');
    }

    public function test_worker_cannot_view_the_status_screen(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $this->actingAs($worker)->get(route('tally-sync.index'))->assertForbidden();
    }

    public function test_owner_can_retry_a_failed_row(): void
    {
        $owner = $this->owner();
        $queue = TallySyncQueue::create(['source_type' => 'x', 'source_id' => 1, 'voucher_type' => 'sales', 'reference_no' => 'SALES-1', 'payload' => [], 'status' => 'failed', 'last_error' => 'Ledger not found', 'attempts' => 2]);

        $this->actingAs($owner)->post(route('tally-sync.retry', $queue))->assertRedirect(route('tally-sync.index'));

        $this->assertDatabaseHas('tally_sync_queue', ['id' => $queue->id, 'status' => 'pending', 'last_error' => null]);
    }

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
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TallySyncStatusScreenTest`
Expected: FAIL — route doesn't exist yet.

- [ ] **Step 3: Write the controller**

```php
<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\TallySyncQueue;

class TallySyncController extends Controller
{
    private const STALE_AFTER_MINUTES = 10;

    public function index()
    {
        $lastCheckin = cache('tally_agent_last_checkin');

        return view('integration.tally-sync.index', [
            'queue' => TallySyncQueue::orderByDesc('id')->paginate(25),
            'lastCheckin' => $lastCheckin,
            'agentIsStale' => $lastCheckin === null || $lastCheckin->diffInMinutes(now()) > self::STALE_AFTER_MINUTES,
        ]);
    }

    public function retry(TallySyncQueue $tallySyncQueue)
    {
        $tallySyncQueue->update(['status' => 'pending', 'last_error' => null]);

        return redirect()->route('tally-sync.index')->with('success', 'Queued for retry.');
    }
}
```

- [ ] **Step 4: Add the routes**

In `routes/web.php`, add inside the existing `Route::middleware('auth')->group(...)` block, near the `accounting.view` group:

```php
    Route::middleware('permission:tally-sync.view')->group(function () {
        Route::get('tally-sync', [App\Http\Controllers\Integration\TallySyncController::class, 'index'])->name('tally-sync.index');
        Route::post('tally-sync/{tallySyncQueue}/retry', [App\Http\Controllers\Integration\TallySyncController::class, 'retry'])->name('tally-sync.retry');
    });
```

- [ ] **Step 5: Write the view**

```blade
@extends('layouts.master')
@section('title') Tally Sync @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Integration @endslot
@slot('title') Tally Sync @endslot
@endcomponent

<x-ui.data-table-card title="Tally Sync Queue">
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

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Voucher Type</th>
                    <th>Status</th>
                    <th>Attempts</th>
                    <th>Last Error</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($queue as $item)
                <tr>
                    <td>{{ $item->reference_no }}</td>
                    <td class="text-capitalize">{{ $item->voucher_type }}</td>
                    <td>
                        @php
                            $badge = $item->status === 'synced' ? 'success' : ($item->status === 'failed' ? 'danger' : 'warning');
                        @endphp
                        <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }}">{{ ucfirst($item->status) }}</span>
                    </td>
                    <td>{{ $item->attempts }}</td>
                    <td>{{ $item->last_error }}</td>
                    <td>
                        @if ($item->status === 'failed')
                        <form method="POST" action="{{ route('tally-sync.retry', $item) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-soft-primary">Retry</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No Tally sync activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $queue->links() }}
</x-ui.data-table-card>
@endsection
```

- [ ] **Step 6: Add the sidebar link**

In `resources/views/layouts/sidebar.blade.php`, add right after the existing `accounting.view` block:

```blade
                @can('accounting.view')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('accounting.*') ? 'active' : '' }}" href="{{ route('accounting.chart') }}">
                        <i class="ri-book-3-line"></i> <span>Accounting</span>
                    </a>
                </li>
                @endcan

                @can('tally-sync.view')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('tally-sync.*') ? 'active' : '' }}" href="{{ route('tally-sync.index') }}">
                        <i class="ri-exchange-line"></i> <span>Tally Sync</span>
                    </a>
                </li>
                @endcan
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=TallySyncStatusScreenTest`
Expected: PASS (5 tests)

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Integration/TallySyncController.php resources/views/integration/tally-sync/index.blade.php routes/web.php resources/views/layouts/sidebar.blade.php tests/Feature/TallySyncStatusScreenTest.php
git commit -m "$(cat <<'EOF'
feat: add Tally Sync status screen for Owner/Account

Lists queued/synced/failed rows with a retry action for failures.
Completes the CRM-side half of the Sales-invoice sync pilot - the
local sync agent (separate plan) is the next piece, built and
validated against a real Tally Prime test company.

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
EOF
)"
```

---

### Task 7: Full verification pass

**Files:** none (verification only)

- [ ] **Step 1: Run the full test suite**

Run: `php artisan test`
Expected: PASS — all tests green, including the 5 new test files from Tasks 1-6 and the full pre-existing suite (per `[[sqlite_vs_mysql_testing_gap]]`, this confirms logic correctness but not real-MySQL schema compatibility — Step 2 covers that).

- [ ] **Step 2: Migrate against live MySQL and re-run the new tests' DB assertions manually**

Run: `php artisan migrate` (against the real `u411614341_Invoice` database, per `[[deployment_operational_notes]]` conventions used elsewhere in this project)
Expected: `tally_sync_queue` table created with no errors (no FK type mismatch is possible here since `source_id` carries no foreign key constraint - see Task 1's migration comment).

Run: `php artisan db:seed --class=RolesAndPermissionsSeeder`
Expected: `tally-sync.view` permission created, attached to Owner and Account roles, no errors.

- [ ] **Step 3: Manually smoke-test the status screen and API against live MySQL**

- Log in as an Owner user, visit `/tally-sync` — should load with an empty queue.
- Create a real invoice through the UI — confirm a new row appears with `status = pending`.
- Issue an agent token: `php artisan tally:create-agent-token {your-email}`.
- `curl -H "Authorization: Bearer {token}" -H "Accept: application/json" https://{host}/api/tally-sync/pending` — confirm the pending invoice is returned.
- `curl -X POST -H "Authorization: Bearer {token}" -H "Accept: application/json" https://{host}/api/tally-sync/{id}/acknowledge -d "status=synced&tally_voucher_id=TEST-1"` — confirm the status screen now shows it as Synced.

- [ ] **Step 4: Report results**

If all steps pass, this plan is complete and ready for the follow-up plan: building the actual local sync agent against a real Tally Prime test company, per the Scope note above.
