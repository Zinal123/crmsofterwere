# BOM / Material Shortage Engine Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give the shop real-time visibility into whether the parts a job or spare-part request needs are actually available, reserve stock against open demand so it can't be double-promised, and check availability at request time — not just when someone tries to fulfill.

**Architecture:** Availability is **derived, never stored** — `available = on_hand − reserved`, where `on_hand` is `invetry.quantity` and `reserved` is computed live from open demand (approved-but-unfulfilled spare-part requests, plus materials on open jobs). A single `AvailabilityService` is the one place that answers "how much of product X is free?". Nothing caches a reserved count, so there is no drift to reconcile. Job material needs are introduced with a new `job_materials` table (jobs have no parts today). Substitution swaps the product on one job-material row without any master BOM.

**Tech Stack:** Laravel 10, PHP 8.2, MySQL (prod) / SQLite (tests), existing repository+service pattern, Blade + Bootstrap 5 views, PHPUnit feature tests.

## Global Constraints

- Availability is **always derived** (`on_hand − reserved`); do not add a stored `reserved_quantity` column.
- Reserved demand statuses: a spare-part request reserves stock while `status IN ('pending','approved')`; a job reserves its materials while open.
- Money/quantity math stays integer for quantities.
- All new permissions go through `RolesAndPermissionsSeeder` and are granted to `Owner`.
- Tests run with `php artisan test`; SQLite in-memory. Reuse `RolesAndPermissionsSeeder` in `setUp()`.
- Every commit message ends with: `Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>`.
- Follow the existing pattern: thin controller → service → repository/model. New services live under `app/Services/Inventory/`.

## Current State (verified 2026-08-13)

- `invetry`: `product_id, quantity, low_stock_threshold, low_stock_notified_at, vandername, rate`. On-hand only, one row per product.
- `spare_part_requests`: `product_id, quantity, status` (`pending|approved|fulfilled|rejected`). `SparePartRequestService::updateStatus()` calls `fulfillFromInventory()` which calls `InventoryService::reduceQuantity()` **only** at the `fulfilled` transition and throws if it would go below zero. **No check at request/approval time.**
- `InventoryService`: `create`, `reduceQuantity($itemId, $qty)` (throws below zero), `setLowStockThreshold`.
- Jobs (`jobs` table) have **no** parts/materials relationship.
- `quotation_items`: `quotation_id, product_id, quantity` (latent demand — out of scope here, noted in Phase 5).

## File Structure

- Create `app/Services/Inventory/AvailabilityService.php` — the single source of "free stock" math. Responsibility: given a product, return on-hand/reserved/available; expose a bulk snapshot.
- Modify `app/Services/Ticketing/SparePartRequestService.php` — block `approved`/`fulfilled` transitions when availability is insufficient.
- Modify `resources/views/ticketing/spare-part-requests/index.blade.php` (or the actual list view) — availability badge per request.
- Modify inventory list view + `InventoryService` — show On hand / Reserved / Available.
- Create `database/migrations/xxxx_create_job_materials_table.php` and `app/Models/JobMaterial.php` — job → required parts.
- Create `app/Services/Job/JobMaterialService.php` — add/remove/substitute materials on a job.
- Modify `app/Http/Controllers/Job/JobController.php` + routes + `resources/views/jobs/show.blade.php` — manage materials + show per-job availability badge.
- Modify `app/Models/Job.php` — `materials()` relation + `overdue()`-style scope for open jobs.
- Tests under `tests/Feature/Inventory/` and `tests/Feature/Job/`.

---

## Phase 1 — Availability foundation

### Task 1: AvailabilityService (derived reserved from approved requests)

**Files:**
- Create: `app/Services/Inventory/AvailabilityService.php`
- Test: `tests/Feature/Inventory/AvailabilityServiceTest.php`

**Interfaces:**
- Produces:
  - `onHand(int $productId): int`
  - `reserved(int $productId): int`
  - `available(int $productId): int` (= onHand − reserved, floored at nothing — may be negative to signal oversell)
  - `snapshot(): \Illuminate\Support\Collection` keyed by `product_id`, each value `['on_hand'=>int,'reserved'=>int,'available'=>int]`

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Feature\Inventory;

use App\Models\Invetry;
use App\Models\Product;
use App\Models\SparePartRequest;
use App\Services\Inventory\AvailabilityService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function product(): Product
    {
        return Product::create(['name' => 'Nozzle', 'rate' => '100', 'unit' => 'pcs', 'make' => 'X']);
    }

    public function test_available_is_on_hand_minus_approved_requests(): void
    {
        $p = $this->product();
        Invetry::create(['product_id' => $p->id, 'quantity' => 10, 'vandername' => 'V', 'rate' => '100']);

        // approved reserves; pending/fulfilled/rejected do not reserve
        SparePartRequest::create(['product_id' => $p->id, 'quantity' => 3, 'status' => 'approved', 'client_machine_id' => 1, 'client_account_id' => 1]);
        SparePartRequest::create(['product_id' => $p->id, 'quantity' => 5, 'status' => 'pending', 'client_machine_id' => 1, 'client_account_id' => 1]);
        SparePartRequest::create(['product_id' => $p->id, 'quantity' => 4, 'status' => 'fulfilled', 'client_machine_id' => 1, 'client_account_id' => 1]);

        $svc = app(AvailabilityService::class);

        $this->assertSame(10, $svc->onHand($p->id));
        $this->assertSame(3, $svc->reserved($p->id));
        $this->assertSame(7, $svc->available($p->id));
    }

    public function test_snapshot_is_keyed_by_product(): void
    {
        $p = $this->product();
        Invetry::create(['product_id' => $p->id, 'quantity' => 8, 'vandername' => 'V', 'rate' => '100']);
        SparePartRequest::create(['product_id' => $p->id, 'quantity' => 2, 'status' => 'approved', 'client_machine_id' => 1, 'client_account_id' => 1]);

        $snap = app(AvailabilityService::class)->snapshot();

        $this->assertSame(8, $snap[$p->id]['on_hand']);
        $this->assertSame(2, $snap[$p->id]['reserved']);
        $this->assertSame(6, $snap[$p->id]['available']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AvailabilityServiceTest`
Expected: FAIL — class `App\Services\Inventory\AvailabilityService` not found.

- [ ] **Step 3: Write minimal implementation**

```php
<?php

namespace App\Services\Inventory;

use App\Models\Invetry;
use App\Models\SparePartRequest;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /** Statuses of a spare-part request that hold (reserve) stock. */
    private const RESERVING_STATUSES = ['approved'];

    public function onHand(int $productId): int
    {
        return (int) Invetry::where('product_id', $productId)->sum('quantity');
    }

    public function reserved(int $productId): int
    {
        return (int) SparePartRequest::where('product_id', $productId)
            ->whereIn('status', self::RESERVING_STATUSES)
            ->sum('quantity');
    }

    public function available(int $productId): int
    {
        return $this->onHand($productId) - $this->reserved($productId);
    }

    /** On-hand/reserved/available for every product that has an inventory row. */
    public function snapshot(): Collection
    {
        $onHand = Invetry::query()
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        $reserved = SparePartRequest::query()
            ->whereIn('status', self::RESERVING_STATUSES)
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        return $onHand->keys()->mapWithKeys(function ($productId) use ($onHand, $reserved) {
            $hand = (int) $onHand[$productId];
            $res = (int) ($reserved[$productId] ?? 0);

            return [$productId => ['on_hand' => $hand, 'reserved' => $res, 'available' => $hand - $res]];
        });
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AvailabilityServiceTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Services/Inventory/AvailabilityService.php tests/Feature/Inventory/AvailabilityServiceTest.php
git commit -m "feat: add AvailabilityService (derived on-hand/reserved/available)

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

---

## Phase 2 — Check availability at request time

### Task 2: Block approval when stock is insufficient

**Files:**
- Modify: `app/Services/Ticketing/SparePartRequestService.php`
- Test: `tests/Feature/Ticketing/SparePartAvailabilityTest.php`

**Interfaces:**
- Consumes: `AvailabilityService::available(int): int` (Task 1).
- Produces: `SparePartRequestService::updateStatus()` throws `\InvalidArgumentException` when transitioning to `approved` (or `fulfilled`) would exceed available stock.

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Feature\Ticketing;

use App\Models\Invetry;
use App\Models\Product;
use App\Models\SparePartRequest;
use App\Services\Ticketing\SparePartRequestService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SparePartAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_cannot_approve_more_than_is_available(): void
    {
        $p = Product::create(['name' => 'Lens', 'rate' => '100', 'unit' => 'pcs', 'make' => 'X']);
        Invetry::create(['product_id' => $p->id, 'quantity' => 2, 'vandername' => 'V', 'rate' => '100']);
        $req = SparePartRequest::create(['product_id' => $p->id, 'quantity' => 5, 'status' => 'pending', 'client_machine_id' => 1, 'client_account_id' => 1]);

        $this->expectException(\InvalidArgumentException::class);
        app(SparePartRequestService::class)->updateStatus($req, 'approved');
    }

    public function test_can_approve_within_available(): void
    {
        $p = Product::create(['name' => 'Lens', 'rate' => '100', 'unit' => 'pcs', 'make' => 'X']);
        Invetry::create(['product_id' => $p->id, 'quantity' => 5, 'vandername' => 'V', 'rate' => '100']);
        $req = SparePartRequest::create(['product_id' => $p->id, 'quantity' => 3, 'status' => 'pending', 'client_machine_id' => 1, 'client_account_id' => 1]);

        $updated = app(SparePartRequestService::class)->updateStatus($req, 'approved');
        $this->assertSame('approved', $updated->status);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SparePartAvailabilityTest`
Expected: FAIL — `test_cannot_approve_more_than_is_available` does not throw (approval currently unchecked).

- [ ] **Step 3: Write minimal implementation**

Inject `AvailabilityService` into `SparePartRequestService` (add to constructor) and guard the `approved` transition. Add near the top of `updateStatus()`, before assigning the new status:

```php
// When approving, reserve stock — but only if it's actually free. Available
// already excludes other approved requests, so this prevents double-promising.
if ($status === 'approved' && $request->status !== 'approved') {
    $available = $this->availabilityService->available($request->product_id);
    if ($request->quantity > $available) {
        throw new \InvalidArgumentException(
            "Only {$available} available for this part — cannot approve a request for {$request->quantity}."
        );
    }
}
```

Constructor change (add the dependency alongside the existing ones):

```php
public function __construct(
    private SparePartRequestRepositoryInterface $repository,
    private InventoryRepositoryInterface $inventoryRepository,
    private InventoryService $inventoryService,
    private \App\Services\Inventory\AvailabilityService $availabilityService,
) {
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=SparePartAvailabilityTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Run the spare-part request suite to check no regression**

Run: `php artisan test --filter=SparePart`
Expected: PASS (all).

- [ ] **Step 6: Commit**

```bash
git add app/Services/Ticketing/SparePartRequestService.php tests/Feature/Ticketing/SparePartAvailabilityTest.php
git commit -m "feat: check availability when approving a spare-part request

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

### Task 3: Availability badge on the spare-part request list

**Files:**
- Modify: `app/Http/Controllers/Ticketing/SparePartRequestController.php` (index method — pass availability snapshot)
- Modify: the spare-part requests admin list view (`resources/views/ticketing/spare-part-requests/index.blade.php` — confirm exact path with `grep -rl "spare-part-requests" resources/views`)
- Test: `tests/Feature/Ticketing/SparePartAvailabilityTest.php` (add a render assertion)

**Interfaces:**
- Consumes: `AvailabilityService::available(int): int` (Task 1).

- [ ] **Step 1: Write the failing test** (append to `SparePartAvailabilityTest`)

```php
public function test_request_list_shows_available_or_not_available_badge(): void
{
    $owner = \App\Models\User::factory()->create();
    $owner->assignRole('Owner');
    $p = Product::create(['name' => 'Ring', 'rate' => '100', 'unit' => 'pcs', 'make' => 'X']);
    Invetry::create(['product_id' => $p->id, 'quantity' => 1, 'vandername' => 'V', 'rate' => '100']);
    SparePartRequest::create(['product_id' => $p->id, 'quantity' => 4, 'status' => 'pending', 'client_machine_id' => 1, 'client_account_id' => 1]);

    $response = $this->actingAs($owner)->get(route('admin.spare-part-requests.index'));

    $response->assertOk();
    $response->assertSee('Not Available');
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=test_request_list_shows_available_or_not_available_badge`
Expected: FAIL — "Not Available" not found.

- [ ] **Step 3: Implement — controller passes availability, view renders badge**

In the controller `index()`, add `'availability' => app(\App\Services\Inventory\AvailabilityService::class)->snapshot()` to the view data.

In the list view, for each request row add:

```blade
@php $free = ($availability[$request->product_id]['available'] ?? 0); @endphp
@if($request->quantity <= $free)
    <span class="badge bg-success-subtle text-success">Available</span>
@else
    <span class="badge bg-danger-subtle text-danger">Not Available</span>
@endif
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=SparePartAvailabilityTest`
Expected: PASS (all).

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Ticketing/SparePartRequestController.php resources/views/ticketing/spare-part-requests/index.blade.php tests/Feature/Ticketing/SparePartAvailabilityTest.php
git commit -m "feat: show availability badge on spare-part request list

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

---

## Phase 3 — Inventory availability display

### Task 4: On hand / Reserved / Available columns in the inventory list

**Files:**
- Modify: `app/Http/Controllers/Inventory/InventryController.php` (index — confirm with `php artisan route:list | grep inventrylist`)
- Modify: the inventory list view (`grep -rl "inventrystore\|quantityupdate" resources/views`)
- Test: `tests/Feature/Inventory/InventoryAvailabilityViewTest.php`

**Interfaces:**
- Consumes: `AvailabilityService::snapshot()` (Task 1).

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Feature\Inventory;

use App\Models\Invetry;
use App\Models\Product;
use App\Models\SparePartRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAvailabilityViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_inventory_list_shows_reserved_and_available(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $p = Product::create(['name' => 'Coil', 'rate' => '100', 'unit' => 'pcs', 'make' => 'X']);
        Invetry::create(['product_id' => $p->id, 'quantity' => 10, 'vandername' => 'V', 'rate' => '100']);
        SparePartRequest::create(['product_id' => $p->id, 'quantity' => 4, 'status' => 'approved', 'client_machine_id' => 1, 'client_account_id' => 1]);

        $response = $this->actingAs($owner)->get(route('invoice.inventrylist'));

        $response->assertOk();
        $response->assertSee('Reserved');
        $response->assertSee('Available');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=InventoryAvailabilityViewTest`
Expected: FAIL — "Reserved"/"Available" not present.

- [ ] **Step 3: Implement — pass snapshot, add columns**

Controller index adds `'availability' => app(\App\Services\Inventory\AvailabilityService::class)->snapshot()`. In the table header add `<th>On Hand</th><th>Reserved</th><th>Available</th>` and per row:

```blade
@php $a = $availability[$item->product_id] ?? ['on_hand' => $item->quantity, 'reserved' => 0, 'available' => $item->quantity]; @endphp
<td class="tabular-nums">{{ $a['on_hand'] }}</td>
<td class="tabular-nums">{{ $a['reserved'] }}</td>
<td class="tabular-nums fw-semibold {{ $a['available'] <= 0 ? 'text-danger' : '' }}">{{ $a['available'] }}</td>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=InventoryAvailabilityViewTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Inventory/InventryController.php resources/views/*inventr* tests/Feature/Inventory/InventoryAvailabilityViewTest.php
git commit -m "feat: show on-hand/reserved/available on the inventory list

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

---

## Phase 4 — Job Bill of Materials

### Task 5: job_materials table + JobMaterial model

**Files:**
- Create: `database/migrations/2026_08_14_000000_create_job_materials_table.php`
- Create: `app/Models/JobMaterial.php`
- Modify: `app/Models/Job.php` (add `materials()` relation)
- Test: `tests/Feature/Job/JobMaterialTest.php`

**Interfaces:**
- Produces: `Job::materials(): HasMany`; `JobMaterial` fillable `job_id, product_id, quantity`.

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\JobMaterial;
use App\Models\Product;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobMaterialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_a_job_has_many_materials(): void
    {
        $job = Job::factory()->create();
        $p = Product::create(['name' => 'Belt', 'rate' => '100', 'unit' => 'pcs', 'make' => 'X']);
        JobMaterial::create(['job_id' => $job->id, 'product_id' => $p->id, 'quantity' => 2]);

        $this->assertCount(1, $job->fresh()->materials);
        $this->assertSame(2, $job->materials->first()->quantity);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=JobMaterialTest`
Expected: FAIL — table/model missing.

- [ ] **Step 3: Migration**

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('product')->restrictOnDelete();
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_materials');
    }
};
```

- [ ] **Step 4: Model + relation**

`app/Models/JobMaterial.php`:

```php
<?php
namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobMaterial extends Model
{
    use Auditable;

    protected $fillable = ['job_id', 'product_id', 'quantity'];

    protected $casts = ['quantity' => 'integer'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
```

Add to `app/Models/Job.php`:

```php
public function materials(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(JobMaterial::class);
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=JobMaterialTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_08_14_000000_create_job_materials_table.php app/Models/JobMaterial.php app/Models/Job.php tests/Feature/Job/JobMaterialTest.php
git commit -m "feat: add job_materials (job bill of materials)

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

### Task 6: Job demand counts toward reserved

**Files:**
- Modify: `app/Services/Inventory/AvailabilityService.php`
- Modify: `app/Models/Job.php` (add `scopeOpen`)
- Test: `tests/Feature/Inventory/AvailabilityServiceTest.php` (add case)

**Interfaces:**
- Produces: `AvailabilityService::reserved()`/`available()`/`snapshot()` now include open-job material demand. `Job::scopeOpen($q)` filters to open statuses.

- [ ] **Step 1: Write the failing test** (append)

```php
public function test_open_job_materials_also_reserve_stock(): void
{
    $p = $this->product();
    \App\Models\Invetry::create(['product_id' => $p->id, 'quantity' => 10, 'vandername' => 'V', 'rate' => '100']);
    $job = \App\Models\Job::factory()->create(['status' => 'in_progress']);
    \App\Models\JobMaterial::create(['job_id' => $job->id, 'product_id' => $p->id, 'quantity' => 4]);
    $done = \App\Models\Job::factory()->create(['status' => 'completed']);
    \App\Models\JobMaterial::create(['job_id' => $done->id, 'product_id' => $p->id, 'quantity' => 3]); // completed = not reserved

    $svc = app(\App\Services\Inventory\AvailabilityService::class);
    $this->assertSame(4, $svc->reserved($p->id));
    $this->assertSame(6, $svc->available($p->id));
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=test_open_job_materials_also_reserve_stock`
Expected: FAIL — reserved is 0 (job demand not counted yet).

- [ ] **Step 3: Implement**

Add to `app/Models/Job.php`:

```php
public function scopeOpen($query)
{
    return $query->whereIn('status', ['pending_approval', 'assigned', 'in_progress', 'on_hold']);
}
```

Update `AvailabilityService` to add job-material demand. Replace `reserved()` and `snapshot()`:

```php
public function reserved(int $productId): int
{
    $fromRequests = (int) SparePartRequest::where('product_id', $productId)
        ->whereIn('status', self::RESERVING_STATUSES)
        ->sum('quantity');

    $fromJobs = (int) \App\Models\JobMaterial::where('product_id', $productId)
        ->whereHas('job', fn ($q) => $q->open())
        ->sum('quantity');

    return $fromRequests + $fromJobs;
}
```

In `snapshot()`, add a second reserved source and merge:

```php
$jobReserved = \App\Models\JobMaterial::query()
    ->whereHas('job', fn ($q) => $q->open())
    ->selectRaw('product_id, SUM(quantity) as qty')
    ->groupBy('product_id')
    ->pluck('qty', 'product_id');
// when building each row: $res = (int)($reserved[$pid] ?? 0) + (int)($jobReserved[$pid] ?? 0);
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AvailabilityServiceTest`
Expected: PASS (all, including earlier cases).

- [ ] **Step 5: Commit**

```bash
git add app/Services/Inventory/AvailabilityService.php app/Models/Job.php tests/Feature/Inventory/AvailabilityServiceTest.php
git commit -m "feat: open-job materials reserve stock in availability math

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

### Task 7: Manage a job's materials (add/remove) + per-job availability badge

**Files:**
- Create: `app/Services/Job/JobMaterialService.php`
- Modify: `routes/web.php` (add `jobs.materials.store`, `jobs.materials.destroy`, gated by `jobs.manage-machines` or a new `jobs.manage-materials` permission — reuse `jobs.approve` if simpler)
- Modify: `app/Http/Controllers/Job/JobController.php` (`addMaterial`, `removeMaterial`)
- Modify: `resources/views/jobs/show.blade.php` (materials list + add form + availability badge)
- Add permission `jobs.manage-materials` in `database/seeders/RolesAndPermissionsSeeder.php`, granted to Owner
- Test: `tests/Feature/Job/JobMaterialManageTest.php`

**Interfaces:**
- Consumes: `AvailabilityService` (Tasks 1/6), `Job::materials()` (Task 5).
- Produces: `JobMaterialService::add(Job $job, int $productId, int $qty): JobMaterial`, `remove(int $materialId): void`, `availabilityFor(Job $job): array` returning `['all_available'=>bool, 'lines'=>Collection<['product_name','required','available','ok']>]`.

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Feature\Job;

use App\Models\Invetry;
use App\Models\Job;
use App\Models\Product;
use App\Models\User;
use App\Services\Job\JobMaterialService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobMaterialManageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_availability_for_flags_shortages(): void
    {
        $job = Job::factory()->create(['status' => 'assigned']);
        $p1 = Product::create(['name' => 'Nozzle', 'rate' => '1', 'unit' => 'pcs', 'make' => 'X']);
        $p2 = Product::create(['name' => 'Lens', 'rate' => '1', 'unit' => 'pcs', 'make' => 'X']);
        Invetry::create(['product_id' => $p1->id, 'quantity' => 5, 'vandername' => 'V', 'rate' => '1']);
        Invetry::create(['product_id' => $p2->id, 'quantity' => 1, 'vandername' => 'V', 'rate' => '1']);

        $svc = app(JobMaterialService::class);
        $svc->add($job, $p1->id, 2); // available (5 >= 2)
        $svc->add($job, $p2->id, 3); // short (1 < 3)

        $result = $svc->availabilityFor($job->fresh());

        $this->assertFalse($result['all_available']);
        $this->assertCount(2, $result['lines']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=JobMaterialManageTest`
Expected: FAIL — `JobMaterialService` not found.

- [ ] **Step 3: Implement the service**

```php
<?php
namespace App\Services\Job;

use App\Models\Job;
use App\Models\JobMaterial;
use App\Services\Inventory\AvailabilityService;
use Illuminate\Support\Collection;

class JobMaterialService
{
    public function __construct(private AvailabilityService $availability)
    {
    }

    public function add(Job $job, int $productId, int $quantity): JobMaterial
    {
        return $job->materials()->create(['product_id' => $productId, 'quantity' => max(1, $quantity)]);
    }

    public function remove(int $materialId): void
    {
        JobMaterial::where('id', $materialId)->delete();
    }

    /**
     * Per-material availability for a job. `available` already nets out this
     * job's own reservation, so add it back before comparing (a job should
     * see itself as satisfiable by the stock it reserved).
     */
    public function availabilityFor(Job $job): array
    {
        $lines = $job->materials()->with('product')->get()->map(function (JobMaterial $m) {
            $free = $this->availability->available($m->product_id) + $m->quantity;

            return [
                'product_name' => $m->product->name ?? 'Unknown part',
                'required' => $m->quantity,
                'available' => $free,
                'ok' => $m->quantity <= $free,
                'material_id' => $m->id,
            ];
        });

        return ['all_available' => $lines->every(fn ($l) => $l['ok']), 'lines' => $lines];
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=JobMaterialManageTest`
Expected: PASS.

- [ ] **Step 5: Add routes, controller methods, permission, and view**

- Add `'jobs.manage-materials'` to `RolesAndPermissionsSeeder::PERMISSIONS` and to Owner's set.
- Routes (in the auth group):

```php
Route::middleware('permission:jobs.manage-materials')->group(function () {
    Route::post('jobs/{job}/materials', [App\Http\Controllers\Job\JobController::class, 'addMaterial'])->name('jobs.materials.store');
    Route::delete('jobs/materials/{material}', [App\Http\Controllers\Job\JobController::class, 'removeMaterial'])->name('jobs.materials.destroy');
});
```

- Controller methods (inject `JobMaterialService`):

```php
public function addMaterial(Request $request, $job, JobMaterialService $svc)
{
    $data = $request->validate(['product_id' => 'required|integer|exists:product,id', 'quantity' => 'required|integer|min:1']);
    $svc->add(Job::findOrFail($job), $data['product_id'], $data['quantity']);
    return back()->with('success', 'Material added to job.');
}

public function removeMaterial($material, JobMaterialService $svc)
{
    $svc->remove((int) $material);
    return back()->with('success', 'Material removed.');
}
```

- In `jobs/show.blade.php`, add a "Materials" card: the add form (product select + qty), the materials list with a per-line badge (Available / Short), and a top-level badge from `availabilityFor($job)['all_available']` ("All materials available" / "Material shortage"). Pass `$materialAvailability = app(JobMaterialService::class)->availabilityFor($job)` from `JobController::show()`.

- [ ] **Step 6: Write the HTTP test for add/remove + badge**

```php
public function test_owner_can_add_a_material_and_see_shortage_badge(): void
{
    $owner = User::factory()->create();
    $owner->assignRole('Owner');
    $job = Job::factory()->create(['status' => 'assigned']);
    $p = Product::create(['name' => 'Gasket', 'rate' => '1', 'unit' => 'pcs', 'make' => 'X']);
    Invetry::create(['product_id' => $p->id, 'quantity' => 1, 'vandername' => 'V', 'rate' => '1']);

    $this->actingAs($owner)->post(route('jobs.materials.store', $job->id), ['product_id' => $p->id, 'quantity' => 5])->assertRedirect();
    $this->assertDatabaseHas('job_materials', ['job_id' => $job->id, 'product_id' => $p->id, 'quantity' => 5]);

    $this->actingAs($owner)->get(route('jobs.show', $job->id))->assertSee('Material shortage');
}
```

- [ ] **Step 7: Run tests**

Run: `php artisan test --filter=JobMaterialManageTest`
Expected: PASS (all).

- [ ] **Step 8: Commit**

```bash
git add app/Services/Job/JobMaterialService.php app/Http/Controllers/Job/JobController.php routes/web.php database/seeders/RolesAndPermissionsSeeder.php resources/views/jobs/show.blade.php tests/Feature/Job/JobMaterialManageTest.php
git commit -m "feat: manage job materials with per-job availability badge

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

### Task 8: Availability badge on the jobs list

**Files:**
- Modify: `app/Http/Controllers/Job/JobController.php` (index — attach availability flag per job)
- Modify: `resources/views/jobs/index.blade.php`
- Test: `tests/Feature/Job/JobMaterialManageTest.php` (add case)

**Interfaces:**
- Consumes: `JobMaterialService::availabilityFor()` (Task 7).

- [ ] **Step 1: Write the failing test** (append)

```php
public function test_jobs_list_shows_material_shortage_badge(): void
{
    $owner = User::factory()->create();
    $owner->assignRole('Owner');
    $job = Job::factory()->create(['status' => 'assigned']);
    $p = Product::create(['name' => 'Coil', 'rate' => '1', 'unit' => 'pcs', 'make' => 'X']);
    Invetry::create(['product_id' => $p->id, 'quantity' => 0, 'vandername' => 'V', 'rate' => '1']);
    \App\Models\JobMaterial::create(['job_id' => $job->id, 'product_id' => $p->id, 'quantity' => 2]);

    $this->actingAs($owner)->get(route('jobs.index'))->assertSee('Shortage');
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=test_jobs_list_shows_material_shortage_badge`
Expected: FAIL.

- [ ] **Step 3: Implement**

In `JobController::index()`, build `$materialStatus = $jobs->mapWithKeys(fn ($j) => [$j->id => app(JobMaterialService::class)->availabilityFor($j)['all_available']])`. In `jobs/index.blade.php`, per job with materials:

```blade
@if(isset($materialStatus[$job->id]))
    @if($materialStatus[$job->id])
        <span class="badge bg-success-subtle text-success"><i class="ri-checkbox-circle-line"></i> Parts ready</span>
    @else
        <span class="badge bg-danger-subtle text-danger"><i class="ri-alert-line"></i> Shortage</span>
    @endif
@endif
```

Note: only render for jobs that have materials (skip when `$job->materials->isEmpty()`), to avoid a misleading "ready" on jobs with no BOM. Guard with `@if($job->materials->isNotEmpty())`. Eager-load `->with('materials')` in the index query to avoid N+1.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=JobMaterialManageTest`
Expected: PASS.

- [ ] **Step 5: Full suite + commit**

```bash
php artisan test
git add app/Http/Controllers/Job/JobController.php resources/views/jobs/index.blade.php tests/Feature/Job/JobMaterialManageTest.php
git commit -m "feat: show material-shortage badge on the jobs list

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

---

## Phase 5 — Substitution (follow-on, optional)

### Task 9: Substitute a job material's product

**Files:**
- Modify: `app/Services/Job/JobMaterialService.php` (`substitute(int $materialId, int $newProductId): JobMaterial`)
- Modify: `app/Http/Controllers/Job/JobController.php` + route `jobs.materials.substitute`
- Modify: `resources/views/jobs/show.blade.php` (substitute control per material line)
- Test: `tests/Feature/Job/JobMaterialManageTest.php`

**Interfaces:**
- Produces: `JobMaterialService::substitute(int $materialId, int $newProductId): JobMaterial` — changes `product_id` on that one job-material row only (no master BOM to edit; the substitution is local to the job).

- [ ] **Step 1: Write the failing test**

```php
public function test_substituting_swaps_the_product_on_that_line_only(): void
{
    $job = Job::factory()->create(['status' => 'assigned']);
    $orig = Product::create(['name' => 'OEM Lens', 'rate' => '1', 'unit' => 'pcs', 'make' => 'X']);
    $alt = Product::create(['name' => 'Compatible Lens', 'rate' => '1', 'unit' => 'pcs', 'make' => 'Y']);
    $mat = \App\Models\JobMaterial::create(['job_id' => $job->id, 'product_id' => $orig->id, 'quantity' => 1]);

    app(JobMaterialService::class)->substitute($mat->id, $alt->id);

    $this->assertDatabaseHas('job_materials', ['id' => $mat->id, 'product_id' => $alt->id]);
}
```

- [ ] **Step 2: Run test to verify it fails / Step 3: implement**

```php
public function substitute(int $materialId, int $newProductId): JobMaterial
{
    $material = JobMaterial::findOrFail($materialId);
    $material->update(['product_id' => $newProductId]);
    return $material;
}
```

Add route `Route::post('jobs/materials/{material}/substitute', ...)->name('jobs.materials.substitute')` under the same `jobs.manage-materials` group, a controller method validating `new_product_id`, and a small "Substitute" dropdown per material line in the view.

- [ ] **Step 4: Run test / Step 5: full suite / Step 6: commit**

```bash
php artisan test
git add -A
git commit -m "feat: allow substituting a job material's product

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

---

## Deployment notes

- New migration (`job_materials`) → `php artisan migrate` on deploy.
- New permission (`jobs.manage-materials`) → re-run `RolesAndPermissionsSeeder`.
- No changes to how stock is decremented at fulfillment (Phase 1–3 only *read* availability; only the existing `fulfilled` transition writes). Job-material consumption on job completion is intentionally **out of scope** — materials reserve while the job is open and release when it leaves open status; wire consumption-on-complete as a later enhancement if the shop wants stock auto-deducted when a job finishes.

## Self-Review notes

- **Spec coverage:** availability-at-request-time (Task 2), auto-reserve (derived: approving reserves — Tasks 1–2; open jobs reserve — Task 6), per-job Available/Not-Available (Tasks 7–8), substitution (Task 9), inventory visibility (Task 4). ✅
- **Derived-reserved constraint** honored throughout — no `reserved_quantity` column.
- **Type consistency:** `available()`/`reserved()`/`onHand()` return `int`; `snapshot()` returns Collection keyed by product_id with `on_hand/reserved/available`; `availabilityFor()` returns `['all_available'=>bool,'lines'=>Collection]` — used consistently in Tasks 6/7/8.
- **Known follow-ups (not gaps):** quotation-item demand and consume-on-complete are deliberately deferred and noted.
```
