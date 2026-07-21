# General Audit Log Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A field-level audit trail (who changed what, from what, to what, when) surfaced against
every record in every section of the app — not one consolidated page.

**Architecture:** One shared polymorphic `audit_logs` table + a reusable `Auditable` trait that
auto-captures `created`/`updated`/`deleted` Eloquent events on any model that uses it. A shared
read stack (`AuditLogService` → `AuditLogRepositoryInterface`) and two shared Blade components
serve every domain identically: `<x-ui.audit-trail>` (inline table, for pages that show one
record) and `<x-ui.audit-trail-modal>` (a per-row "History" button + shared modal, for list
pages). Two known gaps the trait can't see automatically — role assignment and Spatie's own
`Role` model — get one manual log call each, added at their existing call sites.

**Tech Stack:** Laravel 10, Eloquent model events, Blade, a small vanilla-JS fetch (matches this
app's existing modal patterns — no new front-end dependency).

## Global Constraints

- Any migration adding a FK to `users.id` must use plain `integer()` + explicit
  `foreign()->references('id')->on('users')`, never `foreignId()->constrained('users')` — this
  app's live `users.id` is a legacy signed `int`, not Laravel's default unsigned bigint.
  `audit_logs.auditable_id` is NOT a FK to `users.id` (it's polymorphic, no DB-level FK is
  possible across varying target tables) — this constraint applies only to `audit_logs.user_id`.
- `routes/web.php` has a `Route::get('{any}', ...)` catch-all that shadows single-segment GET
  routes registered after it. This plan's one new route (`audit-logs/{type}/{id}`) is
  multi-segment, so it is NOT affected — register it in the general auth-middleware group, not the
  special pre-catch-all block.
- Any test needing an isolated single-role user must use `$user->syncRoles([...])`, never
  `assignRole()` (`UserFactory`'s `afterCreating` hook auto-grants every factory user the Owner
  role; `assignRole()` is additive and leaves Owner's blanket permissions active, silently making
  a "Worker can't see this" test pass for the wrong reason).
- Follow this app's `Controller → Service → Repository` layering
  (`docs/DEVELOPMENT-STANDARDS.md` section 1) — no direct Eloquent calls in controllers.
  `TenantScope` pipes through every repository *read* (currently a no-op seam for future
  multi-tenancy), never writes.
- Passwords are never logged with real old/new values, on any model, ever. This is checked
  explicitly in Task 1's tests and must hold for every later domain task that touches a model with
  a password-like field (only `User`, in this plan).
- **Correction from the design spec, apply everywhere it matters:** the spec's UI table describes
  Attendance and Payroll (`SalaryPayment`) as getting an inline "Audit section," matching Employee
  and Invoice. That's wrong for those two specifically — `attendance/register.blade.php` and
  `payroll/show.blade.php` each render a *list* of many day/payment records for one employee, not
  one record per page. Both get the History-modal-per-row pattern instead, consistent with the
  spec's own stated principle ("list pages get a History icon per row"). Employee and Invoice
  really are one-record-per-page and correctly keep the inline section.

---

### Task 1: Foundation — schema, model, repository, `Auditable` trait

**Files:**
- Create: `database/migrations/2026_07_21_000001_create_audit_logs_table.php`
- Create: `app/Models/AuditLog.php`
- Create: `app/Repositories/Contracts/AuditLogRepositoryInterface.php`
- Create: `app/Repositories/Eloquent/EloquentAuditLogRepository.php`
- Create: `app/Support/Auditing/Auditable.php`
- Modify: `app/Providers/RepositoryServiceProvider.php` (bind the new interface)
- Modify: `app/Providers/AppServiceProvider.php` (register the full morph map)
- Modify: `app/Models/Bank.php` (apply the trait — this model has no CRUD UI, making it the
  cleanest place to exercise create/update/delete without touching any real workflow)
- Test: `tests/Feature/Auditing/AuditableTraitTest.php`

**Interfaces:**
- Produces: `App\Support\Auditing\Auditable` trait (any model `use`s it to get automatic audit
  logging), `App\Repositories\Contracts\AuditLogRepositoryInterface::record(Model $model, string
  $action, ?string $fieldName = null, $oldValue = null, $newValue = null): void` and `::forRecord(string
  $type, int $id): Collection`. Task 2 consumes both.
- A model opts into two optional properties the trait reads: `protected $auditExcept = [...]`
  (fields to log without values — e.g. `password`) and `protected $auditStatusFields = [...]`
  (boolean fields that get `activated`/`deactivated` instead of a generic field-diff row).
  Neither is required — a model with neither property still gets full generic field-diff logging.

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Auditing/AuditableTraitTest.php
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
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Auditing/AuditableTraitTest.php`
Expected: FAIL (table/model/trait don't exist yet)

- [ ] **Step 3: Create the migration**

```php
// database/migrations/2026_07_21_000001_create_audit_logs_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->string('action');
            $table->string('field_name')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            // Plain integer() + explicit foreign(), not foreignId()->constrained(): this app's
            // live users.id column is a legacy signed int, not Laravel's default unsigned
            // bigint - foreignId() creates an FK-incompatible column type against the real
            // table. Nullable so a write with no authenticated actor (a seeder, a console
            // command) doesn't crash on insert.
            $table->integer('user_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
```

- [ ] **Step 4: Create the `AuditLog` model**

```php
// app/Models/AuditLog.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'auditable_type', 'auditable_id', 'action', 'field_name', 'old_value', 'new_value', 'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

- [ ] **Step 5: Create the repository interface and implementation**

```php
// app/Repositories/Contracts/AuditLogRepositoryInterface.php
<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface AuditLogRepositoryInterface
{
    public function record(Model $model, string $action, ?string $fieldName = null, $oldValue = null, $newValue = null): void;

    public function forRecord(string $type, int $id): Collection;
}
```

```php
// app/Repositories/Eloquent/EloquentAuditLogRepository.php
<?php

namespace App\Repositories\Eloquent;

use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function record(Model $model, string $action, ?string $fieldName = null, $oldValue = null, $newValue = null): void
    {
        AuditLog::create([
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'field_name' => $fieldName,
            'old_value' => $oldValue !== null ? (string) $oldValue : null,
            'new_value' => $newValue !== null ? (string) $newValue : null,
            'user_id' => auth()->id(),
        ]);
    }

    public function forRecord(string $type, int $id): Collection
    {
        return $this->tenantScope->apply(
            AuditLog::where('auditable_type', $type)->where('auditable_id', $id)
        )->orderByDesc('created_at')->get();
    }
}
```

- [ ] **Step 6: Bind the repository**

In `app/Providers/RepositoryServiceProvider.php`, add the import
`use App\Repositories\Contracts\AuditLogRepositoryInterface;` and
`use App\Repositories\Eloquent\EloquentAuditLogRepository;` alongside the existing imports, and add
this line inside `register()` alongside the existing bindings:

```php
        $this->app->bind(AuditLogRepositoryInterface::class, EloquentAuditLogRepository::class);
```

- [ ] **Step 7: Create the `Auditable` trait**

```php
// app/Support/Auditing/Auditable.php
<?php

namespace App\Support\Auditing;

use App\Repositories\Contracts\AuditLogRepositoryInterface;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            app(AuditLogRepositoryInterface::class)->record($model, 'created');
        });

        static::updated(function ($model) {
            $repository = app(AuditLogRepositoryInterface::class);
            $except = array_merge(['updated_at'], $model->auditExcept ?? []);
            $statusFields = $model->auditStatusFields ?? [];

            foreach ($model->getDirty() as $key => $newValue) {
                if ($key === 'updated_at') {
                    continue;
                }

                if (in_array($key, $except, true)) {
                    $repository->record($model, 'updated', $key, null, null);
                    continue;
                }

                if (in_array($key, $statusFields, true)) {
                    $repository->record($model, $newValue ? 'activated' : 'deactivated');
                    continue;
                }

                $repository->record($model, 'updated', $key, $model->getOriginal($key), $newValue);
            }
        });

        static::deleted(function ($model) {
            app(AuditLogRepositoryInterface::class)->record($model, 'deleted');
        });
    }
}
```

- [ ] **Step 8: Register the morph map**

In `app/Providers/AppServiceProvider.php`, add `use Illuminate\Database\Eloquent\Relations\Relation;`
to the imports, and add this inside `boot()` (this registers short type-string aliases for every
model this whole plan will eventually cover — declaring them all now means no later domain task
needs to touch this file):

```php
        Relation::morphMap([
            'employee' => \App\Models\Employee::class,
            'employee_document' => \App\Models\EmployeeDocument::class,
            'attendance' => \App\Models\Attendance::class,
            'salary_payment' => \App\Models\SalaryPayment::class,
            'invoice' => \App\Models\Invoice::class,
            'customer' => \App\Models\Customer::class,
            'invoiceproduct' => \App\Models\Invoiceproduct::class,
            'paidamount' => \App\Models\Paidamount::class,
            'product' => \App\Models\Product::class,
            'softerwere' => \App\Models\Softerwere::class,
            'softerwere1' => \App\Models\Softerwere1::class,
            'lasercutting' => \App\Models\Lasercutting::class,
            'fource' => \App\Models\Fource::class,
            'power' => \App\Models\Power::class,
            'motor' => \App\Models\Motor::class,
            'gear' => \App\Models\Gear::class,
            'rack' => \App\Models\Rack::class,
            'cutting' => \App\Models\Cutting::class,
            'cnsthinks' => \App\Models\Cnsthinks::class,
            'termandcondition' => \App\Models\Termandcondition::class,
            'quotation' => \App\Models\Quation::class,
            'inventory' => \App\Models\Invetry::class,
            'user' => \App\Models\User::class,
            'machine' => \App\Models\Machine::class,
            'bank' => \App\Models\Bank::class,
        ]);
```

- [ ] **Step 9: Apply the trait to `Bank`**

In `app/Models/Bank.php`, add `use App\Support\Auditing\Auditable;` to the imports and add
`use Auditable;` as the first line inside the class body (alongside the existing
`use HasApiTokens, HasFactory, Notifiable;` — combine into `use HasApiTokens, HasFactory,
Notifiable, Auditable;`).

- [ ] **Step 10: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Auditing/AuditableTraitTest.php`
Expected: PASS (6/6)

- [ ] **Step 11: Commit**

```bash
git add database/migrations/2026_07_21_000001_create_audit_logs_table.php app/Models/AuditLog.php app/Repositories/Contracts/AuditLogRepositoryInterface.php app/Repositories/Eloquent/EloquentAuditLogRepository.php app/Support/Auditing/Auditable.php app/Providers/RepositoryServiceProvider.php app/Providers/AppServiceProvider.php app/Models/Bank.php tests/Feature/Auditing/AuditableTraitTest.php
git commit -m "feat(audit): add audit_logs schema, Auditable trait, and morph map foundation"
```

---

### Task 2: Shared read stack + inline UI + Employee domain

**Files:**
- Create: `app/Services/Auditing/AuditLogService.php`
- Create: `app/Http/Controllers/Auditing/AuditLogController.php`
- Create: `resources/views/components/ui/audit-trail.blade.php`
- Modify: `routes/web.php` (one new multi-segment route)
- Modify: `app/Models/Employee.php`, `app/Models/EmployeeDocument.php` (apply trait)
- Modify: `database/seeders/RolesAndPermissionsSeeder.php` (+1 permission)
- Modify: `tests/Feature/RolesAndPermissionsSeederTest.php` (count 31 → 32)
- Modify: `resources/views/employees/edit.blade.php` (add Audit section)
- Test: `tests/Feature/Auditing/AuditLogControllerTest.php`
- Test: `tests/Feature/Workforce/EmployeeAuditTest.php`

**Interfaces:**
- Consumes: `AuditLogRepositoryInterface` (Task 1).
- Produces: `AuditLogService::forRecord(string $type, int $id): Collection`,
  `AuditLogService::canView($user, string $type): bool`, `<x-ui.audit-trail :logs="$logs" />`
  component. Every later task in this plan reuses all three without modification.

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Auditing/AuditLogControllerTest.php
<?php

namespace Tests\Feature\Auditing;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permission_can_view_a_records_history(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['name' => 'Audit Target']);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'employee', 'id' => $employee->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $employee = Employee::factory()->create();

        $response = $this->actingAs($worker)->get(route('audit-logs.for-record', ['type' => 'employee', 'id' => $employee->id]));

        $response->assertForbidden();
    }

    public function test_unknown_type_is_forbidden_not_a_server_error(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'not-a-real-type', 'id' => 1]));

        $response->assertForbidden();
    }
}
```

```php
// tests/Feature/Workforce/EmployeeAuditTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_shows_the_employees_audit_trail(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['name' => 'Trail Employee']);
        $employee->update(['department' => 'Fabrication']);

        $response = $this->actingAs($owner)->get(route('employees.edit', $employee->id));

        $response->assertOk();
        $response->assertSee('department');
        $response->assertSee('Fabrication');
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Auditing tests/Feature/Workforce/EmployeeAuditTest.php`
Expected: FAIL (route/permission/service don't exist yet; Employee/EmployeeDocument writes
currently produce no audit rows since neither model uses the trait yet)

- [ ] **Step 3: Add the permission**

In `database/seeders/RolesAndPermissionsSeeder.php`, find the last line of the `PERMISSIONS`
array:

```php
        'employees.view', 'employees.manage', 'attendance.view', 'attendance.manage', 'payroll.view', 'payroll.manage-payments',
    ];
```

Replace with:

```php
        'employees.view', 'employees.manage', 'attendance.view', 'attendance.manage', 'payroll.view', 'payroll.manage-payments',
        'employees.view-audit',
    ];
```

In `tests/Feature/RolesAndPermissionsSeederTest.php`, replace every `31` with `32` (three
occurrences: `assertCount(31, Permission::all())`, `assertCount(31, $owner->permissions)`, and the
second `assertCount(31, Permission::all())` inside `test_seeder_is_idempotent`).

- [ ] **Step 4: Apply the trait to `Employee` and `EmployeeDocument`**

In `app/Models/Employee.php`, add `use App\Support\Auditing\Auditable;` to the imports, add
`use Auditable;` as the first line in the class body (`Employee` currently has `use HasFactory;`
only — combine into `use HasFactory, Auditable;`), and add this property right after the class's
opening `use` line:

```php
    protected $auditStatusFields = ['is_active'];
```

In `app/Models/EmployeeDocument.php`, add the same import and combine
`use Auditable;` in (this model currently has no `use` trait line at all — add
`use App\Support\Auditing\Auditable;` as the first statement inside the class body).

- [ ] **Step 5: Create `AuditLogService`**

```php
// app/Services/Auditing/AuditLogService.php
<?php

namespace App\Services\Auditing;

use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    private const TYPE_PERMISSIONS = [
        'employee' => 'employees.view-audit',
        'employee_document' => 'employees.view-audit',
        'attendance' => 'attendance.view-audit',
        'salary_payment' => 'payroll.view-audit',
        'invoice' => 'invoices.view-audit',
        'customer' => 'invoices.view-audit',
        'invoiceproduct' => 'invoices.view-audit',
        'paidamount' => 'invoices.view-audit',
        'product' => 'products.view-audit',
        'softerwere' => 'products.view-audit',
        'softerwere1' => 'products.view-audit',
        'lasercutting' => 'products.view-audit',
        'fource' => 'products.view-audit',
        'power' => 'products.view-audit',
        'motor' => 'products.view-audit',
        'gear' => 'products.view-audit',
        'rack' => 'products.view-audit',
        'cutting' => 'products.view-audit',
        'cnsthinks' => 'products.view-audit',
        'termandcondition' => 'products.view-audit',
        'quotation' => 'quotations.view-audit',
        'inventory' => 'inventory.view-audit',
        'user' => 'admin.view-audit',
        'machine' => 'machines.view-audit',
    ];

    public function __construct(private AuditLogRepositoryInterface $repository)
    {
    }

    public function canView(Authenticatable $user, string $type): bool
    {
        $permission = self::TYPE_PERMISSIONS[$type] ?? null;

        return $permission !== null && $user->can($permission);
    }

    public function forRecord(string $type, int $id): Collection
    {
        return $this->repository->forRecord($type, $id);
    }

    public function log(Model $model, string $action, ?string $fieldName = null, $oldValue = null, $newValue = null): void
    {
        $this->repository->record($model, $action, $fieldName, $oldValue, $newValue);
    }
}
```

`TYPE_PERMISSIONS` lists every domain this whole plan will eventually cover, even the ones not
wired up until later tasks (`quotations.view-audit`, `products.view-audit`, etc. don't exist in
the seeder yet) — a permission string that doesn't yet exist just means `$user->can(...)`
correctly returns `false` (fails closed) until that domain's task adds it. This is why later
domain tasks don't need to touch this file.

- [ ] **Step 6: Create `AuditLogController` and the route**

```php
// app/Http/Controllers/Auditing/AuditLogController.php
<?php

namespace App\Http\Controllers\Auditing;

use App\Http\Controllers\Controller;
use App\Services\Auditing\AuditLogService;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(private AuditLogService $service)
    {
    }

    public function forRecord(Request $request, string $type, int $id)
    {
        abort_unless($this->service->canView($request->user(), $type), 403);

        return view('components.ui.audit-trail', ['logs' => $this->service->forRecord($type, $id)]);
    }
}
```

In `routes/web.php`, add this route inside the general `Route::middleware('auth')->group(...)`
block used by other multi-segment routes (e.g. near `employees.edit`/`attendance.register` —
this route is multi-segment, so it must NOT go in the pre-catch-all block):

```php
    Route::get('audit-logs/{type}/{id}', [App\Http\Controllers\Auditing\AuditLogController::class, 'forRecord'])->name('audit-logs.for-record');
```

- [ ] **Step 7: Create the `<x-ui.audit-trail>` component**

```blade
{{-- resources/views/components/ui/audit-trail.blade.php --}}
@props(['logs'])
<div class="table-responsive">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Field</th>
                <th>Old Value</th>
                <th>New Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d M Y, H:i') }}</td>
                    <td>{{ $log->user->name ?? 'System' }}</td>
                    <td>{{ ucfirst($log->action) }}</td>
                    <td>{{ $log->field_name ? str_replace('_', ' ', $log->field_name) : '—' }}</td>
                    <td>{{ $log->old_value ?? '—' }}</td>
                    <td>{{ $log->new_value ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><x-ui.empty-state icon="ri-history-line" message="No history recorded yet." /></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
```

- [ ] **Step 8: Wire the Audit section into `employees/edit.blade.php`**

In `resources/views/employees/edit.blade.php`, add this new card immediately before the final
closing `</div>` of the page (after the existing "ID Documents" card, still inside
`@can('employees.view-audit')`):

```blade
    @can('employees.view-audit')
    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Audit Trail</h5>
            <x-ui.audit-trail :logs="app(\App\Services\Auditing\AuditLogService::class)->forRecord('employee', $employee->id)" />
        </div>
    </div>
    @endcan
```

- [ ] **Step 9: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Auditing tests/Feature/Workforce/EmployeeAuditTest.php`
Expected: PASS (4/4)

- [ ] **Step 10: Run the full Workforce suite to confirm no regressions**

Run: `php artisan test --filter=Workforce`
Expected: all pass (this includes every pre-existing Workforce test plus the new one).

- [ ] **Step 11: Commit**

```bash
git add app/Services/Auditing/AuditLogService.php app/Http/Controllers/Auditing/AuditLogController.php resources/views/components/ui/audit-trail.blade.php routes/web.php app/Models/Employee.php app/Models/EmployeeDocument.php database/seeders/RolesAndPermissionsSeeder.php tests/Feature/RolesAndPermissionsSeederTest.php resources/views/employees/edit.blade.php tests/Feature/Auditing/AuditLogControllerTest.php tests/Feature/Workforce/EmployeeAuditTest.php
git commit -m "feat(audit): shared read stack, inline audit-trail component, and Employee domain wiring"
```

---

### Task 3: Shared History-modal component + Attendance domain

**Files:**
- Create: `resources/views/components/ui/audit-trail-modal.blade.php`
- Modify: `app/Models/Attendance.php` (apply trait)
- Modify: `database/seeders/RolesAndPermissionsSeeder.php` (+1 permission)
- Modify: `tests/Feature/RolesAndPermissionsSeederTest.php` (count 32 → 33)
- Modify: `resources/views/attendance/register.blade.php` (add History button per row)
- Test: `tests/Feature/Workforce/AttendanceAuditTest.php`

**Interfaces:**
- Consumes: `AuditLogService`/route from Task 2.
- Produces: `<x-ui.audit-trail-modal type="..." />` — one shared modal, driven by a per-row
  trigger button carrying `data-audit-id`. Every remaining task in this plan (Payroll, Product,
  Quotation, Inventory, Admin, Machines) reuses this exact component without modification, each
  just adding its own `<x-ui.audit-trail-modal type="..." />` include once per page plus a
  trigger button per row.

**Context:** `attendance/register.blade.php` shows one employee's whole month of attendance —
many `Attendance` rows on one page, not one record. This needs the per-row History pattern (see
this plan's Global Constraints — a correction from the design spec, which described this as an
inline section).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Workforce/AttendanceAuditTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_attendance_twice_logs_the_status_change(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $attendance = Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-01', 'status' => 'present', 'marked_by' => $owner->id]);

        $attendance->update(['status' => 'absent']);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'attendance', 'id' => $attendance->id]));

        $response->assertOk();
        $response->assertSee('present');
        $response->assertSee('absent');
    }

    public function test_register_page_has_a_history_trigger_per_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $attendance = Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-10', 'status' => 'present', 'marked_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('attendance.register', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $attendance->id . '"', false);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Workforce/AttendanceAuditTest.php`
Expected: FAIL

- [ ] **Step 3: Add the permission**

Same mechanical change as Task 2 Step 3: append `'attendance.view-audit',` to the `PERMISSIONS`
array in `database/seeders/RolesAndPermissionsSeeder.php`, and replace every `32` with `33` in
`tests/Feature/RolesAndPermissionsSeederTest.php`.

- [ ] **Step 4: Apply the trait to `Attendance`**

In `app/Models/Attendance.php`, add `use App\Support\Auditing\Auditable;` to the imports. This
model currently has no `use` trait line — add `use Auditable;` as the first statement inside the
class body.

- [ ] **Step 5: Create the shared `<x-ui.audit-trail-modal>` component**

```blade
{{-- resources/views/components/ui/audit-trail-modal.blade.php --}}
@props(['type'])
<div class="modal fade" id="auditTrailModal-{{ $type }}" tabindex="-1" aria-labelledby="auditTrailModalLabel-{{ $type }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="auditTrailModalLabel-{{ $type }}">History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="auditTrailBody-{{ $type }}">
                <p class="text-muted mb-0">Loading…</p>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('auditTrailModal-{{ $type }}').addEventListener('show.bs.modal', function (event) {
    var recordId = event.relatedTarget.getAttribute('data-audit-id');
    var body = document.getElementById('auditTrailBody-{{ $type }}');
    body.innerHTML = '<p class="text-muted mb-0">Loading…</p>';

    fetch('{{ url('audit-logs/' . $type) }}/' + recordId)
        .then(function (response) { return response.text(); })
        .then(function (html) { body.innerHTML = html; })
        .catch(function () { body.innerHTML = '<p class="text-danger mb-0">Failed to load history.</p>'; });
});
</script>
```

- [ ] **Step 6: Add a History trigger to each row + include the modal once, in `attendance/register.blade.php`**

Find the row template:

```blade
                    @forelse($attendanceRows as $row)
                        <tr>
                            <td>{{ $row->date->toDateString() }}</td>
                            <td>{{ str_replace('_', ' ', $row->status) }}</td>
                            <td>{{ $row->overtime_hours }}</td>
                        </tr>
                    @empty
```

Replace with (adds a History column):

```blade
                    @forelse($attendanceRows as $row)
                        <tr>
                            <td>{{ $row->date->toDateString() }}</td>
                            <td>{{ str_replace('_', ' ', $row->status) }}</td>
                            <td>{{ $row->overtime_hours }}</td>
                            <td>
                                @can('attendance.view-audit')
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#auditTrailModal-attendance" data-audit-id="{{ $row->id }}">History</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
```

Find the table header:

```blade
                <thead><tr><th>Date</th><th>Status</th><th>Overtime Hours</th></tr></thead>
```

Replace with:

```blade
                <thead><tr><th>Date</th><th>Status</th><th>Overtime Hours</th><th></th></tr></thead>
```

Find the `colspan="3"` on the empty-state row:

```blade
                        <tr><td colspan="3"><x-ui.empty-state icon="ri-calendar-line" message="No attendance marked for this month yet." /></td></tr>
```

Replace with:

```blade
                        <tr><td colspan="4"><x-ui.empty-state icon="ri-calendar-line" message="No attendance marked for this month yet." /></td></tr>
```

Add the modal once, immediately before the final `@endsection`:

```blade
@can('attendance.view-audit')
    <x-ui.audit-trail-modal type="attendance" />
@endcan
```

- [ ] **Step 7: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Workforce/AttendanceAuditTest.php`
Expected: PASS (2/2)

- [ ] **Step 8: Run the full Workforce suite**

Run: `php artisan test --filter=Workforce`
Expected: all pass, no regressions.

- [ ] **Step 9: Commit**

```bash
git add resources/views/components/ui/audit-trail-modal.blade.php app/Models/Attendance.php database/seeders/RolesAndPermissionsSeeder.php tests/Feature/RolesAndPermissionsSeederTest.php resources/views/attendance/register.blade.php tests/Feature/Workforce/AttendanceAuditTest.php
git commit -m "feat(audit): shared History-modal component and Attendance domain wiring"
```

---

### Task 4: Payroll (`SalaryPayment`) domain

**Files:**
- Modify: `app/Models/SalaryPayment.php` (apply trait)
- Modify: `database/seeders/RolesAndPermissionsSeeder.php` (+1 permission)
- Modify: `tests/Feature/RolesAndPermissionsSeederTest.php` (count 33 → 34)
- Modify: `resources/views/payroll/show.blade.php` (History button per row)
- Test: `tests/Feature/Workforce/PayrollAuditTest.php`

**Interfaces:**
- Consumes: `<x-ui.audit-trail-modal>` (Task 3), unchanged.

**Context:** Same reasoning as Attendance — `payroll/show.blade.php`'s payments table lists many
`SalaryPayment` rows for one employee/month, so this gets the History-modal-per-row pattern.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Workforce/PayrollAuditTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\Employee;
use App\Models\SalaryPayment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_a_payment_logs_a_created_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $payment = SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-15', 'amount' => 2000, 'paid_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'salary_payment', 'id' => $payment->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_payroll_page_has_a_history_trigger_per_payment_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $payment = SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-15', 'amount' => 2000, 'paid_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $payment->id . '"', false);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Workforce/PayrollAuditTest.php`
Expected: FAIL

- [ ] **Step 3: Add the permission**

Append `'payroll.view-audit',` to `PERMISSIONS` in `RolesAndPermissionsSeeder.php`; replace every
`33` with `34` in `tests/Feature/RolesAndPermissionsSeederTest.php`.

- [ ] **Step 4: Apply the trait to `SalaryPayment`**

In `app/Models/SalaryPayment.php`, add `use App\Support\Auditing\Auditable;` to the imports. This
model currently has no `use` trait line — add `use Auditable;` as the first statement inside the
class body.

- [ ] **Step 5: Add a History trigger to each row in `payroll/show.blade.php`**

Find:

```blade
                <thead><tr><th>Date</th><th>Amount</th><th>Note</th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->date->toDateString() }}</td>
                            <td>{{ $payment->amount }}</td>
                            <td>{{ $payment->note }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-ui.empty-state icon="ri-money-rupee-circle-line" message="No payments recorded this month yet." /></td></tr>
                    @endforelse
                </tbody>
```

Replace with:

```blade
                <thead><tr><th>Date</th><th>Amount</th><th>Note</th><th></th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->date->toDateString() }}</td>
                            <td>{{ $payment->amount }}</td>
                            <td>{{ $payment->note }}</td>
                            <td>
                                @can('payroll.view-audit')
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#auditTrailModal-salary_payment" data-audit-id="{{ $payment->id }}">History</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-ui.empty-state icon="ri-money-rupee-circle-line" message="No payments recorded this month yet." /></td></tr>
                    @endforelse
                </tbody>
```

Add the modal once, immediately before the final `@endsection`:

```blade
@can('payroll.view-audit')
    <x-ui.audit-trail-modal type="salary_payment" />
@endcan
```

- [ ] **Step 6: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Workforce/PayrollAuditTest.php`
Expected: PASS (2/2)

- [ ] **Step 7: Run the full Workforce suite**

Run: `php artisan test --filter=Workforce`
Expected: all pass.

- [ ] **Step 8: Commit**

```bash
git add app/Models/SalaryPayment.php database/seeders/RolesAndPermissionsSeeder.php tests/Feature/RolesAndPermissionsSeederTest.php resources/views/payroll/show.blade.php tests/Feature/Workforce/PayrollAuditTest.php
git commit -m "feat(audit): wire Payroll (SalaryPayment) domain into the audit trail"
```

---

### Task 5: Invoice domain (`Invoice`, `Customer`, `Invoiceproduct`, `Paidamount`)

**Files:**
- Modify: `app/Models/Invoice.php`, `app/Models/Customer.php`, `app/Models/Invoiceproduct.php`,
  `app/Models/Paidamount.php` (apply trait to all four)
- Modify: `database/seeders/RolesAndPermissionsSeeder.php` (+1 permission)
- Modify: `tests/Feature/RolesAndPermissionsSeederTest.php` (count 34 → 35)
- Modify: `resources/views/apps-invoices-details.blade.php` (inline Audit section)
- Test: `tests/Feature/InvoiceAuditTest.php`

**Interfaces:**
- Consumes: `<x-ui.audit-trail>` (Task 2), unchanged.

**Context:** `apps-invoices-details.blade.php` is a single invoice's printable detail page — one
record per page, same shape as Employee's edit page. It correctly gets the inline section, not a
modal. The page is styled for printing/PDF export (see the `html2canvas`/`jspdf` scripts at the
bottom) — the Audit section must not appear in a printed/exported copy, so it needs Bootstrap's
`d-print-none` utility class.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/InvoiceAuditTest.php
<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_details_page_shows_the_invoices_audit_trail(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $invoice = Invoice::factory()->create(['amount' => 50000, 'amountwithtax' => 59000]);
        Customer::factory()->create(['invoice_id' => $invoice->id, 'state' => 'Gujarat']);
        $invoice->update(['paidamount' => 20000]);

        $response = $this->actingAs($owner)->get(route('invoice.details', $invoice->id));

        $response->assertOk();
        $response->assertSee('paidamount');
        $response->assertSee('d-print-none', false);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/InvoiceAuditTest.php`
Expected: FAIL

- [ ] **Step 3: Add the permission**

Append `'invoices.view-audit',` to `PERMISSIONS`; replace every `34` with `35` in
`tests/Feature/RolesAndPermissionsSeederTest.php`.

- [ ] **Step 4: Apply the trait to all four models**

Each of `app/Models/Invoice.php`, `app/Models/Customer.php`, `app/Models/Invoiceproduct.php`,
`app/Models/Paidamount.php` currently declares `use HasApiTokens, HasFactory, Notifiable;` as its
first class-body statement. In each of the four files: add
`use App\Support\Auditing\Auditable;` to the imports, and change that line to
`use HasApiTokens, HasFactory, Notifiable, Auditable;`.

- [ ] **Step 5: Wire the Audit section into `apps-invoices-details.blade.php`**

Find the closing structure near the end of the file:

```blade
        <div class="row text-center">
          <span>This is a Computer Generated Invoice</span>
        </div>
      </div>
 </div>
```

Replace with (adds the Audit section right after the printable content, wrapped in
`d-print-none` so it's excluded from print/PDF export):

```blade
        <div class="row text-center">
          <span>This is a Computer Generated Invoice</span>
        </div>
      </div>
 </div>

 @can('invoices.view-audit')
 <div class="card mt-3 d-print-none">
     <div class="card-body">
         <h5 class="card-title">Audit Trail</h5>
         <x-ui.audit-trail :logs="app(\App\Services\Auditing\AuditLogService::class)->forRecord('invoice', $invoice[0]->id)" />
     </div>
 </div>
 @endcan
```

Note `$invoice[0]->id`, not `$invoice->id` — `InvoiceService::getInvoiceDetails()` (see
`app/Services/Invoice/InvoiceService.php`) passes `$invoice` as the raw collection returned by
`getInvoiceRecords($id)`, and the rest of this view already accesses it the same way (e.g. the
existing `Grand Total` row uses `@foreach($invoice as $item4)`).

- [ ] **Step 6: Run tests, confirm pass**

Run: `php artisan test tests/Feature/InvoiceAuditTest.php`
Expected: PASS (1/1)

- [ ] **Step 7: Run the full Invoice suite**

Run: `php artisan test --filter=Invoice`
Expected: all pass, no regressions.

- [ ] **Step 8: Commit**

```bash
git add app/Models/Invoice.php app/Models/Customer.php app/Models/Invoiceproduct.php app/Models/Paidamount.php database/seeders/RolesAndPermissionsSeeder.php tests/Feature/RolesAndPermissionsSeederTest.php resources/views/apps-invoices-details.blade.php tests/Feature/InvoiceAuditTest.php
git commit -m "feat(audit): wire Invoice domain (Invoice/Customer/Invoiceproduct/Paidamount) into the audit trail"
```

---

### Task 6: Product domain (12 models)

**Files:**
- Modify: `app/Models/Product.php`, `Softerwere.php`, `Softerwere1.php`, `Lasercutting.php`,
  `Fource.php`, `Power.php`, `Motor.php`, `Gear.php`, `Rack.php`, `Cutting.php`, `Cnsthinks.php`,
  `Termandcondition.php` (apply trait to all twelve)
- Modify: `database/seeders/RolesAndPermissionsSeeder.php` (+1 permission)
- Modify: `tests/Feature/RolesAndPermissionsSeederTest.php` (count 35 → 36)
- Modify: `resources/views/product.blade.php` (History trigger in the existing dropdown menu)
- Test: `tests/Feature/ProductAuditTest.php`

**Interfaces:**
- Consumes: `<x-ui.audit-trail-modal>` (Task 3), unchanged.

**Context:** None of the 10 product-config models (`Softerwere` through `Cnsthinks`) or
`Termandcondition` have any update/delete route today — `ProductConfigController` only has
`*store()` (create) actions, and `Termandcondition` has no repository/controller wiring
whatsoever. Only `Product` itself has both create and delete (`ProductController::delete()` does
a real `Product::find($id)->delete()`). Write the tests to match what's actually reachable: a
create-event test for one config model plus `Termandcondition` (proving the trait is safely
applied even with no live write path yet), and create+delete tests for `Product` itself. Do not
write an "update" test for any of these twelve models — there is no code path that would exercise
it, and a test asserting behavior nothing in the app can trigger would be testing nothing real.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/ProductAuditTest.php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Softerwere;
use App\Models\Termandcondition;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_product_logs_a_created_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $product = Product::create(['name' => 'Test Machine', 'rate' => 100000]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'product', 'id' => $product->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_deleting_a_product_logs_a_deleted_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $product = Product::create(['name' => 'To Delete']);
        $product->delete();

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'product', 'id' => $product->id]));

        $response->assertOk();
        $response->assertSee('deleted');
    }

    public function test_creating_a_product_config_row_logs_a_created_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $product = Product::create(['name' => 'Config Parent']);
        $software = Softerwere::create(['product_id' => $product->id]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'softerwere', 'id' => $software->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_creating_a_term_and_condition_row_logs_a_created_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $term = Termandcondition::create([]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'termandcondition', 'id' => $term->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_product_list_has_a_history_trigger_per_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $product = Product::create(['name' => 'List Row Product']);

        $response = $this->actingAs($owner)->get(route('product'));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $product->id . '"', false);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/ProductAuditTest.php`
Expected: FAIL (`Termandcondition::create([])` is valid — passing no keys never triggers a
mass-assignment exception regardless of that model's `$fillable`/`$guarded`; confirmed its
`$fillable` is `['company', 'product_id', 'modal', 'logo', 'image', 'description']` with
`$guarded = []`, so the empty-array create used in the test above is safe as written).

One more thing worth knowing, not part of this task: `Termandcondition::$table` is
`'softwaredetails'` — the exact same underlying table `Softerwere::$table` points to. Two
different Eloquent models mapping to one physical table is a pre-existing oddity unrelated to
audit logging (each model's own `$fillable` differs, so they aren't interchangeable) — document,
don't fix; it doesn't affect this task since the morph map keys on PHP class, not table name, so
`'softerwere'` and `'termandcondition'` audit rows stay correctly distinguishable by which model
actually performed the write.

- [ ] **Step 3: Add the permission**

Append `'products.view-audit',` to `PERMISSIONS`; replace every `35` with `36` in
`tests/Feature/RolesAndPermissionsSeederTest.php`.

- [ ] **Step 4: Apply the trait to all twelve models**

Eleven of the twelve — `Softerwere.php`, `Softerwere1.php`, `Lasercutting.php`, `Fource.php`,
`Power.php`, `Motor.php`, `Gear.php`, `Rack.php`, `Cutting.php`, `Cnsthinks.php`,
`Termandcondition.php` — are byte-identical in structure (confirmed): each has
`use HasApiTokens, HasFactory, Notifiable;` as its only class-body trait line, with no other
differences relevant to this change. In each of these eleven files: add
`use App\Support\Auditing\Auditable;` to the imports, and change
`use HasApiTokens, HasFactory, Notifiable;` to `use HasApiTokens, HasFactory, Notifiable,
Auditable;`.

`Product.php` has the same shape (see this task's file list) — apply the identical change there
too, making twelve.

- [ ] **Step 5: Add a History trigger to `product.blade.php`'s existing action dropdown**

Find:

```blade
                                <ul class="dropdown-menu dropdown-menu-end">

                                    <li>
                                        <a class="dropdown-item remove-item-btn" href="{{route('product.delete' ,$item->id)}}">
                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                        </a>
                                    </li>
                                </ul>
```

Replace with:

```blade
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @can('products.view-audit')
                                    <li>
                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#auditTrailModal-product" data-audit-id="{{ $item->id }}">
                                            <i class="ri-history-line align-bottom me-2 text-muted"></i> History
                                        </a>
                                    </li>
                                    @endcan
                                    <li>
                                        <a class="dropdown-item remove-item-btn" href="{{route('product.delete' ,$item->id)}}">
                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                        </a>
                                    </li>
                                </ul>
```

Add the modal once, immediately before the final `@endsection`:

```blade
@can('products.view-audit')
    <x-ui.audit-trail-modal type="product" />
@endcan
```

- [ ] **Step 6: Run tests, confirm pass**

Run: `php artisan test tests/Feature/ProductAuditTest.php`
Expected: PASS (5/5)

- [ ] **Step 7: Run the full app suite**

Run: `php artisan test`
Expected: all pass, no regressions (this is the first task touching 12 models at once — worth the
full suite, not just a filtered slice).

- [ ] **Step 8: Commit**

```bash
git add app/Models/Product.php app/Models/Softerwere.php app/Models/Softerwere1.php app/Models/Lasercutting.php app/Models/Fource.php app/Models/Power.php app/Models/Motor.php app/Models/Gear.php app/Models/Rack.php app/Models/Cutting.php app/Models/Cnsthinks.php app/Models/Termandcondition.php database/seeders/RolesAndPermissionsSeeder.php tests/Feature/RolesAndPermissionsSeederTest.php resources/views/product.blade.php tests/Feature/ProductAuditTest.php
git commit -m "feat(audit): wire Product domain (Product + 11 config models) into the audit trail"
```

---

### Task 7: Quotation domain

**Files:**
- Modify: `app/Models/Quation.php` (apply trait)
- Modify: `database/seeders/RolesAndPermissionsSeeder.php` (+1 permission)
- Modify: `tests/Feature/RolesAndPermissionsSeederTest.php` (count 36 → 37)
- Modify: `resources/views/listquation.blade.php` (History button per row)
- Test: `tests/Feature/QuotationAuditTest.php`

**Interfaces:**
- Consumes: `<x-ui.audit-trail-modal>` (Task 3), unchanged.

**Context:** `EloquentQuotationRepository` only has `create()` — there is no update/delete path
for `Quation` anywhere in the app today. Write only a create-event test, same reasoning as the
product-config models in Task 6.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/QuotationAuditTest.php
<?php

namespace Tests\Feature;

use App\Models\Quation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_quotation_logs_a_created_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $quotation = Quation::create(['clientname' => 'Test Client']);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'quotation', 'id' => $quotation->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_quotation_list_has_a_history_trigger_per_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $quotation = Quation::create(['clientname' => 'List Row Client']);

        $response = $this->actingAs($owner)->get(route('listqutation'));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $quotation->id . '"', false);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/QuotationAuditTest.php`
Expected: FAIL

- [ ] **Step 3: Add the permission**

Append `'quotations.view-audit',` to `PERMISSIONS`; replace every `36` with `37` in
`tests/Feature/RolesAndPermissionsSeederTest.php`.

- [ ] **Step 4: Apply the trait to `Quation`**

In `app/Models/Quation.php`, add `use App\Support\Auditing\Auditable;` to the imports, and change
`use HasApiTokens, HasFactory, Notifiable;` to `use HasApiTokens, HasFactory, Notifiable,
Auditable;`.

- [ ] **Step 5: Add a History trigger to `listquation.blade.php`**

Find:

```blade
                            <td>
                                   <a href="{{route('quation.pdf' ,$item->id)}}" class="btn btn-success">Download Qutation</a>
                                   <button type="button" class="btn btn-danger" id = "delete" data-bs-toggle="modal" data-id="{{$item->id}}">
                                     Delete
                                   </button>
                                    
                            </td>
```

Replace with:

```blade
                            <td>
                                   <a href="{{route('quation.pdf' ,$item->id)}}" class="btn btn-success">Download Qutation</a>
                                   <button type="button" class="btn btn-danger" id = "delete" data-bs-toggle="modal" data-id="{{$item->id}}">
                                     Delete
                                   </button>
                                   @can('quotations.view-audit')
                                   <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#auditTrailModal-quotation" data-audit-id="{{$item->id}}">
                                     History
                                   </button>
                                   @endcan
                            </td>
```

Add the modal once, immediately before the final `@endsection`:

```blade
@can('quotations.view-audit')
    <x-ui.audit-trail-modal type="quotation" />
@endcan
```

- [ ] **Step 6: Run tests, confirm pass**

Run: `php artisan test tests/Feature/QuotationAuditTest.php`
Expected: PASS (2/2)

- [ ] **Step 7: Run the full app suite**

Run: `php artisan test`
Expected: all pass.

- [ ] **Step 8: Commit**

```bash
git add app/Models/Quation.php database/seeders/RolesAndPermissionsSeeder.php tests/Feature/RolesAndPermissionsSeederTest.php resources/views/listquation.blade.php tests/Feature/QuotationAuditTest.php
git commit -m "feat(audit): wire Quotation domain into the audit trail"
```

---

### Task 8: Inventory domain

**Files:**
- Modify: `app/Models/Invetry.php` (apply trait)
- Modify: `database/seeders/RolesAndPermissionsSeeder.php` (+1 permission)
- Modify: `tests/Feature/RolesAndPermissionsSeederTest.php` (count 37 → 38)
- Modify: `resources/views/inventrylist.blade.php` (History button per row)
- Test: `tests/Feature/InventoryAuditTest.php`

**Interfaces:**
- Consumes: `<x-ui.audit-trail-modal>` (Task 3), unchanged.

**Context:** Unlike Quotation/Product-config, `Invetry` genuinely has both create AND update
(`EloquentInventoryRepository::updateQuantity()` does a real `$item->quantity = $newQuantity;
$item->save();`) — write both a create and an update test.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/InventoryAuditTest.php
<?php

namespace Tests\Feature;

use App\Models\Invetry;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_inventory_row_logs_a_created_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $item = Invetry::create(['product_id' => 1, 'quantity' => 10]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'inventory', 'id' => $item->id]));

        $response->assertOk();
        $response->assertSee('created');
    }

    public function test_updating_quantity_logs_the_old_and_new_value(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $item = Invetry::create(['product_id' => 1, 'quantity' => 10]);

        $item->quantity = 4;
        $item->save();

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'inventory', 'id' => $item->id]));

        $response->assertOk();
        $response->assertSee('10');
        $response->assertSee('4');
    }

    public function test_inventory_list_has_a_history_trigger_per_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $item = Invetry::create(['product_id' => 1, 'quantity' => 5]);

        $response = $this->actingAs($owner)->get(route('invoice.inventrylist'));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $item->id . '"', false);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/InventoryAuditTest.php`
Expected: FAIL

- [ ] **Step 3: Add the permission**

Append `'inventory.view-audit',` to `PERMISSIONS`; replace every `37` with `38` in
`tests/Feature/RolesAndPermissionsSeederTest.php`.

- [ ] **Step 4: Apply the trait to `Invetry`**

In `app/Models/Invetry.php`, add `use App\Support\Auditing\Auditable;` to the imports, and change
`use HasFactory;` to `use HasFactory, Auditable;`.

- [ ] **Step 5: Add a History trigger to `inventrylist.blade.php`**

Find:

```blade
                                  <td>
                                    <div class="d-flex gap-2">
                                     <div class="remove">
                                     <button type="button" class="btn btn-sm btn-primary open-modal" data-id="{{ $item->id }}"data-bs-toggle="modal" data-bs-target="#exampleModalgrid1">
                                        Quantity Update
                                    </button>
                                    </div>
                                    </div>
                                    </td>
```

Replace with:

```blade
                                  <td>
                                    <div class="d-flex gap-2">
                                     <div class="remove">
                                     <button type="button" class="btn btn-sm btn-primary open-modal" data-id="{{ $item->id }}"data-bs-toggle="modal" data-bs-target="#exampleModalgrid1">
                                        Quantity Update
                                    </button>
                                    </div>
                                    @can('inventory.view-audit')
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#auditTrailModal-inventory" data-audit-id="{{ $item->id }}">
                                        History
                                    </button>
                                    @endcan
                                    </div>
                                    </td>
```

Add the modal once, immediately before the final `@endsection`:

```blade
@can('inventory.view-audit')
    <x-ui.audit-trail-modal type="inventory" />
@endcan
```

- [ ] **Step 6: Run tests, confirm pass**

Run: `php artisan test tests/Feature/InventoryAuditTest.php`
Expected: PASS (3/3)

- [ ] **Step 7: Run the full app suite**

Run: `php artisan test`
Expected: all pass.

- [ ] **Step 8: Commit**

```bash
git add app/Models/Invetry.php database/seeders/RolesAndPermissionsSeeder.php tests/Feature/RolesAndPermissionsSeederTest.php resources/views/inventrylist.blade.php tests/Feature/InventoryAuditTest.php
git commit -m "feat(audit): wire Inventory domain into the audit trail"
```

---

### Task 9: Admin domain — `User` model + manual Role logging

**Files:**
- Modify: `app/Models/User.php` (add `$auditStatusFields` only — `Auditable`/`$auditExcept` already
  landed in Task 1, see the correction note below)
- Modify: `app/Services/Admin/UserService.php` (manual log call for role assignment)
- Modify: `app/Services/Admin/RoleService.php` (manual log calls for role create/delete/permission
  toggle — this is the "Role is a vendor class" gap from the design spec)
- Modify: `database/seeders/RolesAndPermissionsSeeder.php` (+1 permission)
- Modify: `tests/Feature/RolesAndPermissionsSeederTest.php` (count 38 → 39)
- Modify: `resources/views/admin/users.blade.php` (History button per row)
- Test: `tests/Feature/Admin/UserAuditTest.php`

**Interfaces:**
- Consumes: `AuditLogService::log()` (Task 2) — this is the first task that calls it directly from
  a service, rather than relying purely on the automatic trait.

**Correction, read before starting:** Task 1's own brief test
(`test_excluded_field_logs_the_change_with_no_values`) turned out to require `User` to already
carry `Auditable` — the plan had a genuine internal contradiction (that test can't pass with only
`Bank` audited, per Task 1's own Step 10 pass requirement). Task 1's implementer correctly applied
`use Auditable;` and `protected $auditExcept = ['password'];` to `app/Models/User.php` already, to
make its own brief's test pass. **Do not re-apply the trait or re-add `$auditExcept` — both
already exist.** This task's only remaining change to that file is adding
`$auditStatusFields` (Step 4 below), which Task 1 correctly didn't add since nothing in its own
scope needed it. The rest of this task (permission, UI, `UserService`/`RoleService` changes) is
unaffected and unchanged.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Admin/UserAuditTest.php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivating_a_user_logs_it(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $target = User::factory()->create();
        $target->syncRoles(['Owner']);

        $this->actingAs($owner)->put(route('admin.users.update', $target->id), [
            'role' => 'Owner',
            'is_active' => '0',
        ]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'user', 'id' => $target->id]));

        $response->assertOk();
        $response->assertSee('deactivated');
    }

    public function test_changing_a_users_role_logs_the_old_and_new_role(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $target = User::factory()->create();
        $target->syncRoles(['Worker']);

        $this->actingAs($owner)->put(route('admin.users.update', $target->id), [
            'role' => 'Owner',
            'is_active' => '1',
        ]);

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'user', 'id' => $target->id]));

        $response->assertOk();
        $response->assertSee('Worker');
        $response->assertSee('Owner');
    }

    public function test_users_list_has_a_history_trigger_per_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $owner->id . '"', false);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Admin/UserAuditTest.php`
Expected: FAIL

- [ ] **Step 3: Add the permission**

Append `'admin.view-audit',` to `PERMISSIONS`; replace every `38` with `39` in
`tests/Feature/RolesAndPermissionsSeederTest.php`.

- [ ] **Step 4: Add status-field special-casing to `User`**

`app/Models/User.php` already has `Auditable` applied and `protected $auditExcept = ['password'];`
(added ahead of schedule in Task 1 — see the correction note above). It's still missing the
`is_active` status-field special-casing every other domain in this plan has (Employee, Machine).
Add this property immediately after the existing `protected $auditExcept = ['password'];` line:

```php
    protected $auditStatusFields = ['is_active'];
```

- [ ] **Step 5: Add the manual role-change log call in `UserService`**

In `app/Services/Admin/UserService.php`, add
`use App\Services\Auditing\AuditLogService;` to the imports, add
`private AuditLogService $auditLog` as a second constructor-promoted parameter, and log the role
change before it's overwritten. Find:

```php
    public function updateRoleAndStatus($id, string $roleName, bool $isActive): User
    {
        $user = $this->repository->find($id);

        if ($user === null) {
            throw new \InvalidArgumentException('User not found.');
        }

        $losingOwnerRole = $user->hasRole('Owner') && $roleName !== 'Owner';
        $beingDeactivated = $user->is_active && !$isActive;

        if (($losingOwnerRole || $beingDeactivated) && $this->isLastActiveOwner($user)) {
            throw new \InvalidArgumentException('At least one active Owner must remain. Assign another user the Owner role before changing or deactivating this one.');
        }

        $user->syncRoles([$roleName]);
        $user->is_active = $isActive;
        $this->repository->save($user);

        return $user;
    }
```

Replace with:

```php
    public function updateRoleAndStatus($id, string $roleName, bool $isActive): User
    {
        $user = $this->repository->find($id);

        if ($user === null) {
            throw new \InvalidArgumentException('User not found.');
        }

        $losingOwnerRole = $user->hasRole('Owner') && $roleName !== 'Owner';
        $beingDeactivated = $user->is_active && !$isActive;

        if (($losingOwnerRole || $beingDeactivated) && $this->isLastActiveOwner($user)) {
            throw new \InvalidArgumentException('At least one active Owner must remain. Assign another user the Owner role before changing or deactivating this one.');
        }

        $oldRole = $user->roles->pluck('name')->first();

        if ($oldRole !== $roleName) {
            $this->auditLog->log($user, 'updated', 'role', $oldRole, $roleName);
        }

        $user->syncRoles([$roleName]);
        $user->is_active = $isActive;
        $this->repository->save($user);

        return $user;
    }
```

And update the constructor:

```php
    public function __construct(private UserRepositoryInterface $repository)
    {
    }
```

to:

```php
    public function __construct(
        private UserRepositoryInterface $repository,
        private AuditLogService $auditLog,
    ) {
    }
```

- [ ] **Step 6: Add manual Role logging in `RoleService`**

In `app/Services/Admin/RoleService.php`, add `use App\Services\Auditing\AuditLogService;` to the
imports, add `private AuditLogService $auditLog` as a second constructor-promoted parameter (same
pattern as Step 5), and add logging calls. `AuditLogService::log()` requires an `Illuminate\
Database\Eloquent\Model` as its first argument (see Task 1's trait signature) — Spatie's `Role`
extends `Model`, so it's a valid target even though it's a vendor class, not one of this app's own
models. Find:

```php
    public function createRole(string $name): Role
    {
        return $this->repository->create($name);
    }

    public function deleteRole($id): void
    {
        $role = $this->repository->find($id);

        if ($role === null) {
            throw new \InvalidArgumentException('Role not found.');
        }

        if ($role->name === 'Owner') {
            throw new \InvalidArgumentException('The Owner role cannot be deleted.');
        }

        if ($this->repository->hasAssignedUsers($id)) {
            throw new \InvalidArgumentException('This role has users assigned to it. Reassign those users before deleting the role.');
        }

        $this->repository->delete($id);
    }

    public function togglePermission($roleId, string $permissionName): bool
    {
        $role = $this->repository->find($roleId);

        if ($role === null) {
            throw new \InvalidArgumentException('Role not found.');
        }

        if (!in_array($permissionName, RolesAndPermissionsSeeder::PERMISSIONS, true)) {
            throw new \InvalidArgumentException('Invalid permission name.');
        }

        Permission::findOrCreate($permissionName);

        if ($role->hasPermissionTo($permissionName)) {
            $role->revokePermissionTo($permissionName);
            return false;
        }

        $role->givePermissionTo($permissionName);
        return true;
    }
```

Replace with:

```php
    public function createRole(string $name): Role
    {
        $role = $this->repository->create($name);
        $this->auditLog->log($role, 'created');

        return $role;
    }

    public function deleteRole($id): void
    {
        $role = $this->repository->find($id);

        if ($role === null) {
            throw new \InvalidArgumentException('Role not found.');
        }

        if ($role->name === 'Owner') {
            throw new \InvalidArgumentException('The Owner role cannot be deleted.');
        }

        if ($this->repository->hasAssignedUsers($id)) {
            throw new \InvalidArgumentException('This role has users assigned to it. Reassign those users before deleting the role.');
        }

        $this->auditLog->log($role, 'deleted');
        $this->repository->delete($id);
    }

    public function togglePermission($roleId, string $permissionName): bool
    {
        $role = $this->repository->find($roleId);

        if ($role === null) {
            throw new \InvalidArgumentException('Role not found.');
        }

        if (!in_array($permissionName, RolesAndPermissionsSeeder::PERMISSIONS, true)) {
            throw new \InvalidArgumentException('Invalid permission name.');
        }

        Permission::findOrCreate($permissionName);

        if ($role->hasPermissionTo($permissionName)) {
            $role->revokePermissionTo($permissionName);
            $this->auditLog->log($role, 'updated', 'permission', $permissionName, null);
            return false;
        }

        $role->givePermissionTo($permissionName);
        $this->auditLog->log($role, 'updated', 'permission', null, $permissionName);
        return true;
    }
```

And update the constructor:

```php
    public function __construct(private RoleRepositoryInterface $repository)
    {
    }
```

to:

```php
    public function __construct(
        private RoleRepositoryInterface $repository,
        private AuditLogService $auditLog,
    ) {
    }
```

Note: `Role` isn't in the `Relation::morphMap()` registered in Task 1 — add
`'role' => \Spatie\Permission\Models\Role::class,` to that map now (in
`app/Providers/AppServiceProvider.php`), since this is the first task that actually creates an
`AuditLog` row with `Role` as the target. Without this, `$role->getMorphClass()` would fall back
to the full vendor class name string instead of a short alias, which would still work (the type
column would just contain `Spatie\Permission\Models\Role` instead of `role`) but breaks this
plan's convention of short type strings everywhere else — add the map entry for consistency, even
though `Role` audit history isn't surfaced in any UI in this plan (no per-role detail page exists
to view it from; the log rows exist for a future page or direct DB inspection).

- [ ] **Step 7: Add a History trigger to `admin/users.blade.php`**

Find:

```blade
                            <td>
                                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" class="form-select form-select-sm">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ $user->roles->pluck('name')->first() === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="is_active" value="{{ $user->is_active ? '0' : '1' }}">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
```

Replace with:

```blade
                            <td>
                                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" class="form-select form-select-sm">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ $user->roles->pluck('name')->first() === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="is_active" value="{{ $user->is_active ? '0' : '1' }}">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                @can('admin.view-audit')
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" data-bs-toggle="modal" data-bs-target="#auditTrailModal-user" data-audit-id="{{ $user->id }}">History</button>
                                @endcan
                            </td>
```

Add the modal once, immediately before the final closing `</div>` of the page content (this file
has no `@endsection` — it's included content, matching the layout structure already established
for the two-column `admin/users.blade.php` page):

```blade
@can('admin.view-audit')
    <x-ui.audit-trail-modal type="user" />
@endcan
```

- [ ] **Step 8: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Admin/UserAuditTest.php`
Expected: PASS (3/3)

- [ ] **Step 9: Run the full app suite**

Run: `php artisan test`
Expected: all pass — this task changes `UserService`/`RoleService` constructors, so specifically
re-check `tests/Feature/Admin/*` and any role-matrix tests from the earlier role-matrix feature
still pass with the added constructor dependency.

- [ ] **Step 10: Commit**

```bash
git add app/Models/User.php app/Services/Admin/UserService.php app/Services/Admin/RoleService.php app/Providers/AppServiceProvider.php database/seeders/RolesAndPermissionsSeeder.php tests/Feature/RolesAndPermissionsSeederTest.php resources/views/admin/users.blade.php tests/Feature/Admin/UserAuditTest.php
git commit -m "feat(audit): wire Admin domain (User + manual Role logging) into the audit trail"
```

---

### Task 10: Machines domain

**Files:**
- Modify: `app/Models/Machine.php` (apply trait)
- Modify: `database/seeders/RolesAndPermissionsSeeder.php` (+1 permission)
- Modify: `tests/Feature/RolesAndPermissionsSeederTest.php` (count 39 → 40)
- Modify: `resources/views/machines/index.blade.php` (History button per row)
- Test: `tests/Feature/Job/MachineAuditTest.php`

**Interfaces:**
- Consumes: `<x-ui.audit-trail-modal>` (Task 3), unchanged.

**Context:** This domain was a proposed addition during spec review (Machine CRUD exists in the
Job Tracking module but isn't covered by the separate `job_audit_logs` system). `Machine::
$fillable = ['name', 'is_active']` — `is_active` is already the special-cased status field,
matching the same pattern as Employee/User.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Job/MachineAuditTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_toggling_a_machine_off_logs_deactivated(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::create(['name' => 'Fiber Laser #1', 'is_active' => true]);

        $this->actingAs($owner)->post(route('machines.toggle', $machine->id));

        $response = $this->actingAs($owner)->get(route('audit-logs.for-record', ['type' => 'machine', 'id' => $machine->id]));

        $response->assertOk();
        $response->assertSee('deactivated');
    }

    public function test_machines_list_has_a_history_trigger_per_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $machine = Machine::create(['name' => 'CO2 Laser #1', 'is_active' => true]);

        $response = $this->actingAs($owner)->get(route('machines.index'));

        $response->assertOk();
        $response->assertSee('data-audit-id="' . $machine->id . '"', false);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Job/MachineAuditTest.php`
Expected: FAIL

- [ ] **Step 3: Add the permission**

Append `'machines.view-audit',` to `PERMISSIONS`; replace every `39` with `40` in
`tests/Feature/RolesAndPermissionsSeederTest.php`.

- [ ] **Step 4: Apply the trait to `Machine`**

In `app/Models/Machine.php`, add `use App\Support\Auditing\Auditable;` to the imports, change
`use HasFactory;` to `use HasFactory, Auditable;`, and add this property right after the class's
opening `use` line:

```php
    protected $auditStatusFields = ['is_active'];
```

- [ ] **Step 5: Add a History trigger to `machines/index.blade.php`**

Find:

```blade
                        <td>
                            <form action="{{ route('machines.toggle', $machine->id) }}" method="POST">
                                @csrf
                                <x-ui.button variant="secondary" size="sm" type="submit">
                                    {{ $machine->is_active ? 'Disable' : 'Enable' }}
                                </x-ui.button>
                            </form>
                        </td>
```

Replace with:

```blade
                        <td>
                            <form action="{{ route('machines.toggle', $machine->id) }}" method="POST" class="d-inline">
                                @csrf
                                <x-ui.button variant="secondary" size="sm" type="submit">
                                    {{ $machine->is_active ? 'Disable' : 'Enable' }}
                                </x-ui.button>
                            </form>
                            @can('machines.view-audit')
                            <x-ui.button variant="secondary" size="sm" type="button" data-bs-toggle="modal" data-bs-target="#auditTrailModal-machine" data-audit-id="{{ $machine->id }}">History</x-ui.button>
                            @endcan
                        </td>
```

Add the modal once, immediately before the final `@endsection`:

```blade
@can('machines.view-audit')
    <x-ui.audit-trail-modal type="machine" />
@endcan
```

- [ ] **Step 6: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Job/MachineAuditTest.php`
Expected: PASS (2/2)

- [ ] **Step 7: Run the full app suite — this is the last domain task**

Run: `php artisan test`
Expected: all pass, including every prior audit test and the pre-existing suite.

- [ ] **Step 8: Commit**

```bash
git add app/Models/Machine.php database/seeders/RolesAndPermissionsSeeder.php tests/Feature/RolesAndPermissionsSeederTest.php resources/views/machines/index.blade.php tests/Feature/Job/MachineAuditTest.php
git commit -m "feat(audit): wire Machines domain into the audit trail"
```

---

### Task 11: Final verification

**Files:** none (verification only).

- [ ] **Step 1: Run the complete test suite**

Run: `php artisan test`
Expected: all pass, including every audit test added across Tasks 1-10 and the entire
pre-existing suite (baseline before this plan: 168 passing).

- [ ] **Step 2: Verify the permission count landed correctly**

Run: `php artisan tinker --execute="echo \Spatie\Permission\Models\Permission::count();"`
against a real migrated database, or re-check
`tests/Feature/RolesAndPermissionsSeederTest.php`'s final assertion — expected final count is 40
(31 baseline + 9 new `.view-audit` permissions, one per domain task).

- [ ] **Step 3: Migrate and smoke-test against live MySQL**

Per this app's standing sqlite-vs-MySQL testing-gap convention — every task's automated tests ran
against sqlite only. Run `php artisan migrate` against the real local/live database to confirm the
`audit_logs` migration applies cleanly (specifically: confirm the `user_id` FK against the real
`users` table succeeds, the one column type this whole plan is most likely to get wrong given this
app's recurring signed-int legacy `users.id` issue). Then, through a real authenticated session
(or `tinker` with `auth()->login(...)`), perform at least one real write in three different
domains (e.g. update an Employee's department, toggle a Machine's status, create an Invetry row)
and confirm each produces the expected `audit_logs` row(s) and that each domain's History
button/Audit section renders without error against the live data.

- [ ] **Step 4: Update `docs/DEVELOPMENT-STANDARDS.md`**

Add a short note to section 1 (Architecture) or as a new numbered section, documenting the
`Auditable` trait as a standing convention: any new model going forward that has a create/update/
delete UI should `use Auditable;` and, if applicable, declare `$auditExcept`/`$auditStatusFields`
— matching how `TenantScope` is already documented as a standing per-repository convention.
