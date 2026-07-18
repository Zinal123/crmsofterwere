# Job Tracking Module Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a self-contained Job Tracking module — workers request or receive jobs, complete them with photo + GPS proof, every action is recorded in an immutable audit trail — without touching any existing module, table, or route.

**Architecture:** Follows this codebase's established Controller → Service → Repository pattern exactly (see `docs/DEVELOPMENT-STANDARDS.md`), a new `Job` domain alongside the existing Product/Inventory/Quotation/Invoice/Admin domains, reusing the design-system component library (`<x-ui.button>`, `<x-ui.status-badge>`, `<x-ui.confirm-modal>`, `<x-ui.empty-state>`, `<x-ui.data-table-card>`) already in the codebase.

**Tech Stack:** Laravel 10, Blade, spatie/laravel-permission (existing), PHP GD extension (built-in, no new dependency) for photo compression, `guzzlehttp/guzzle` (existing) for reverse geocoding, `laravel-notification-channels/webpush` (**one new composer dependency**, Task 11 only) for Web Push.

Reference spec: `docs/superpowers/specs/2026-07-18-job-tracking-design.md` — read it before starting; this plan implements it task by task.

## Global Constraints

- **No existing table, route, controller, or view is modified**, except two additive, backward-compatible touches: `resources/views/layouts/topbar.blade.php` (Task 10 — swap dead demo notification markup for real data, same DOM structure) and `app/Services/Home/DashboardService.php` + `resources/views/index.blade.php` (Task 14 — add a widget row, don't remove anything).
- Every repository **read** goes through `App\Support\Tenancy\TenantScope::apply()` (already exists, no-op today) — same convention as every other domain. Writes don't go through it.
- Status enum values (exact strings, used in DB, PHP, and tests — never change casing): `pending_approval`, `assigned`, `in_progress`, `on_hold`, `completed`, `rejected`.
- Permission strings (exact, added to `Database\Seeders\RolesAndPermissionsSeeder::PERMISSIONS`): `jobs.view-own`, `jobs.create`, `jobs.view-all`, `jobs.approve`, `jobs.assign`, `jobs.manage-machines`.
- `Owner` role gets all six job permissions (matches existing seeder pattern — Owner gets every permission). `Worker` role gets `jobs.view-own` and `jobs.create` only.
- Route middleware for "worker-or-manager" pages uses spatie's pipe-OR syntax: `permission:jobs.view-own|jobs.view-all` (spatie/laravel-permission's `PermissionMiddleware` treats `|`-separated permissions as OR — confirmed supported in the installed `spatie/laravel-permission: ^6.0`).
- All new migrations must run cleanly against both MySQL (live) and sqlite (`RefreshDatabase` in tests) — no raw driver-specific DDL.
- Every task's tests live in `tests/Feature/Job/*Test.php`, use `RefreshDatabase`, and use `database/factories/*Factory.php`.
- `on_hold_reason`, `rejection_reason`, and `completion_notes` are all **required** at the point their status transition happens — enforced in the Service layer (return/throw a validation-style error the Controller turns into a 422 or redirect-back-with-errors, matching the existing `InvoiceService`/`ProductController` validation convention).
- The Job **create form is shared** between worker-request and manager-direct-assign (a deliberate simplification confirmed in this plan, not a spec deviation): a user with `jobs.assign` sees an extra "Assign to" dropdown. If filled in, the job is created directly as `assigned` with `assigned_to` set to the chosen worker. If the form is submitted by a user without `jobs.assign` (or the dropdown is left blank), the job is created as `pending_approval` with `assigned_to` defaulting to the creator.

---

### Task 1: Migrations, Models, Factories

**Files:**
- Create: `database/migrations/2026_07_18_000001_create_machines_table.php`
- Create: `database/migrations/2026_07_18_000002_create_jobs_table.php`
- Create: `database/migrations/2026_07_18_000003_create_job_photos_table.php`
- Create: `database/migrations/2026_07_18_000004_create_job_audit_logs_table.php`
- Create: `database/migrations/2026_07_18_000005_create_push_subscriptions_table.php`
- Create: `app/Models/Machine.php`
- Create: `app/Models/Job.php`
- Create: `app/Models/JobPhoto.php`
- Create: `app/Models/JobAuditLog.php`
- Create: `database/factories/MachineFactory.php`
- Create: `database/factories/JobFactory.php`
- Test: `tests/Feature/Job/JobModelsMigrationTest.php`

**Interfaces:**
- Produces: `Machine` (fillable: `name`, `is_active`), `Job` (fillable: `title`, `description`, `machine_id`, `site_name`, `created_by`, `assigned_to`, `priority`, `due_date`, `status`, `decided_by`, `decided_at`, `rejection_reason`, `on_hold_reason`, `completion_notes`, `completed_at`), `JobPhoto` (fillable: `job_id`, `uploaded_by`, `path`, `latitude`, `longitude`, `location_captured`, `map_link`, `address`, `captured_at`), `JobAuditLog` (fillable: `job_id`, `user_id`, `action`, `description`, `metadata`; **no `updated_at`**).
- Relationships: `Job::machine()` belongsTo `Machine`; `Job::photos()` hasMany `JobPhoto`; `Job::auditLogs()` hasMany `JobAuditLog`; `Job::creator()` belongsTo `User` via `created_by`; `Job::assignee()` belongsTo `User` via `assigned_to`; `Machine::jobs()` hasMany `Job`.

- [ ] **Step 1: Write the migrations**

```php
// database/migrations/2026_07_18_000001_create_machines_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
```

```php
// database/migrations/2026_07_18_000002_create_jobs_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete();
            $table->string('site_name')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending_approval', 'assigned', 'in_progress', 'on_hold', 'completed', 'rejected']);
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('on_hold_reason')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
```

```php
// database/migrations/2026_07_18_000003_create_job_photos_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('path');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('location_captured')->default(false);
            $table->string('map_link')->nullable();
            $table->string('address')->nullable();
            $table->timestamp('captured_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_photos');
    }
};
```

```php
// database/migrations/2026_07_18_000004_create_job_audit_logs_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_audit_logs');
    }
};
```

```php
// database/migrations/2026_07_18_000005_create_push_subscriptions_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('subscribable_type');
            $table->unsignedBigInteger('subscribable_id');
            $table->text('endpoint');
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('content_encoding')->nullable();
            $table->timestamps();

            $table->index(['subscribable_type', 'subscribable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
```

- [ ] **Step 2: Write the models**

```php
// app/Models/Machine.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
}
```

```php
// app/Models/Job.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'machine_id', 'site_name', 'created_by', 'assigned_to',
        'priority', 'due_date', 'status', 'decided_by', 'decided_at', 'rejection_reason',
        'on_hold_reason', 'completion_notes', 'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'decided_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function photos()
    {
        return $this->hasMany(JobPhoto::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(JobAuditLog::class);
    }
}
```

```php
// app/Models/JobPhoto.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPhoto extends Model
{
    protected $fillable = [
        'job_id', 'uploaded_by', 'path', 'latitude', 'longitude',
        'location_captured', 'map_link', 'address', 'captured_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'location_captured' => 'boolean',
        'captured_at' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
```

```php
// app/Models/JobAuditLog.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobAuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['job_id', 'user_id', 'action', 'description', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException('JobAuditLog rows are immutable and cannot be updated.');
        });

        static::deleting(function () {
            throw new \LogicException('JobAuditLog rows are immutable and cannot be deleted.');
        });
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

- [ ] **Step 3: Write the factories**

```php
// database/factories/MachineFactory.php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MachineFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['CNC Lathe #1', 'Fiber Laser Cutter', 'CNC Mill #2', 'Plasma Cutter']),
            'is_active' => true,
        ];
    }
}
```

```php
// database/factories/JobFactory.php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    public function definition()
    {
        $creator = User::factory()->create();

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'site_name' => $this->faker->company(),
            'created_by' => $creator->id,
            'assigned_to' => $creator->id,
            'priority' => 'medium',
            'status' => 'pending_approval',
        ];
    }
}
```

- [ ] **Step 4: Write a migration/model smoke test**

```php
// tests/Feature/Job/JobModelsMigrationTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\JobAuditLog;
use App\Models\JobPhoto;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobModelsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_job_tables_migrate_and_relate_correctly(): void
    {
        $machine = Machine::factory()->create();
        $job = Job::factory()->create(['machine_id' => $machine->id]);
        $photo = JobPhoto::create([
            'job_id' => $job->id,
            'uploaded_by' => $job->created_by,
            'path' => 'job-photos/test.jpg',
            'captured_at' => now(),
        ]);
        $log = JobAuditLog::create([
            'job_id' => $job->id,
            'user_id' => $job->created_by,
            'action' => 'created',
            'description' => 'Job created',
        ]);

        $this->assertTrue($job->machine->is($machine));
        $this->assertTrue($job->photos->first()->is($photo));
        $this->assertTrue($job->auditLogs->first()->is($log));
    }

    public function test_job_audit_log_cannot_be_updated_or_deleted(): void
    {
        $job = Job::factory()->create();
        $log = JobAuditLog::create([
            'job_id' => $job->id,
            'user_id' => $job->created_by,
            'action' => 'created',
            'description' => 'Job created',
        ]);

        $this->expectException(\LogicException::class);
        $log->update(['description' => 'tampered']);
    }
}
```

- [ ] **Step 5: Run the tests**

Run: `php artisan test tests/Feature/Job/JobModelsMigrationTest.php`
Expected: PASS (2/2)

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_07_18_* app/Models/Machine.php app/Models/Job.php app/Models/JobPhoto.php app/Models/JobAuditLog.php database/factories/MachineFactory.php database/factories/JobFactory.php tests/Feature/Job/JobModelsMigrationTest.php
git commit -m "feat(jobs): add migrations, models, and factories for job tracking domain"
```

---

### Task 2: Permissions + Machines CRUD

**Files:**
- Modify: `database/seeders/RolesAndPermissionsSeeder.php`
- Create: `app/Repositories/Contracts/MachineRepositoryInterface.php`
- Create: `app/Repositories/Eloquent/EloquentMachineRepository.php`
- Create: `app/Services/Job/MachineService.php`
- Create: `app/Http/Controllers/Job/MachineController.php`
- Create: `resources/views/machines/index.blade.php`
- Modify: `app/Providers/RepositoryServiceProvider.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/sidebar.blade.php`
- Test: `tests/Feature/Job/MachineTest.php`

**Interfaces:**
- Produces: `MachineRepositoryInterface::all(): Collection`, `::create(array $data): Machine`, `::toggleActive($id): Machine`. `MachineService` mirrors these 1:1.
- Consumes: existing `TenantScope` (constructor-inject into the Eloquent repository, same as `EloquentProductRepository`).

- [ ] **Step 1: Add the six job permissions to the seeder**

```php
// database/seeders/RolesAndPermissionsSeeder.php — add to the PERMISSIONS array, after 'admin.manage-users',
        'jobs.view-own', 'jobs.create', 'jobs.view-all', 'jobs.approve', 'jobs.assign', 'jobs.manage-machines',
```

```php
// database/seeders/RolesAndPermissionsSeeder.php — in run(), after $owner->syncPermissions(...) and before Role::findOrCreate('Worker'):
        $worker = Role::findOrCreate('Worker');
        $worker->syncPermissions(['jobs.view-own', 'jobs.create']);
```

Remove the old bare `Role::findOrCreate('Worker');` line — it's now replaced by the two lines above (a role sync is idempotent and safe to re-run, matching this seeder's existing idempotency).

- [ ] **Step 2: Write a failing test for permission seeding**

```php
// tests/Feature/Job/MachineTest.php (top of file, first test — rest of the file added in Step 6)
<?php

namespace Tests\Feature\Job;

use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_role_gets_view_own_and_create_job_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = \Spatie\Permission\Models\Role::findByName('Worker');

        $this->assertTrue($worker->hasPermissionTo('jobs.view-own'));
        $this->assertTrue($worker->hasPermissionTo('jobs.create'));
        $this->assertFalse($worker->hasPermissionTo('jobs.manage-machines'));
    }
}
```

- [ ] **Step 3: Run it, confirm it fails**

Run: `php artisan test tests/Feature/Job/MachineTest.php`
Expected: FAIL (permission `jobs.view-own` does not exist / Worker role has no such permission)

- [ ] **Step 4: Repository interface + implementation**

```php
// app/Repositories/Contracts/MachineRepositoryInterface.php
<?php

namespace App\Repositories\Contracts;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Collection;

interface MachineRepositoryInterface
{
    public function all(): Collection;

    public function create(array $data): Machine;

    public function find($id): ?Machine;

    public function toggleActive($id): Machine;
}
```

```php
// app/Repositories/Eloquent/EloquentMachineRepository.php
<?php

namespace App\Repositories\Eloquent;

use App\Models\Machine;
use App\Repositories\Contracts\MachineRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentMachineRepository implements MachineRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function all(): Collection
    {
        return $this->tenantScope->apply(Machine::orderBy('name'))->get();
    }

    public function create(array $data): Machine
    {
        return Machine::create($data);
    }

    public function find($id): ?Machine
    {
        return $this->tenantScope->apply(Machine::query())->find($id);
    }

    public function toggleActive($id): Machine
    {
        $machine = Machine::findOrFail($id);
        $machine->is_active = ! $machine->is_active;
        $machine->save();

        return $machine;
    }
}
```

- [ ] **Step 5: Service, Controller, view, route, sidebar link**

```php
// app/Services/Job/MachineService.php
<?php

namespace App\Services\Job;

use App\Models\Machine;
use App\Repositories\Contracts\MachineRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MachineService
{
    public function __construct(private MachineRepositoryInterface $repository)
    {
    }

    public function list(): Collection
    {
        return $this->repository->all();
    }

    public function create(array $data): Machine
    {
        return $this->repository->create($data);
    }

    public function toggleActive($id): Machine
    {
        return $this->repository->toggleActive($id);
    }
}
```

```php
// app/Http/Controllers/Job/MachineController.php
<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Services\Job\MachineService;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function __construct(private MachineService $service)
    {
    }

    public function index()
    {
        return view('machines.index', ['machines' => $this->service->list()]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $this->service->create($request->only('name'));

        return redirect()->route('machines.index')->with('success', 'Machine added.');
    }

    public function toggle($id)
    {
        $this->service->toggleActive($id);

        return redirect()->route('machines.index')->with('success', 'Machine updated.');
    }
}
```

```blade
{{-- resources/views/machines/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.data-table-card title="Machines">
        <table class="table table-bordered align-middle" id="machinesTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($machines as $machine)
                    <tr>
                        <td>{{ $machine->name }}</td>
                        <td>
                            <x-ui.status-badge
                                :status="$machine->is_active ? 'Active' : 'Inactive'"
                                :variant="$machine->is_active ? 'success' : 'secondary'"
                                icon="{{ $machine->is_active ? 'ri-checkbox-circle-line' : 'ri-close-circle-line' }}" />
                        </td>
                        <td>
                            <form action="{{ route('machines.toggle', $machine->id) }}" method="POST">
                                @csrf
                                <x-ui.button variant="secondary" size="sm" type="submit">
                                    {{ $machine->is_active ? 'Disable' : 'Enable' }}
                                </x-ui.button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-ui.empty-state icon="ri-tools-line" message="No machines added yet." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.data-table-card>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Add Machine</h5>
            <form action="{{ route('machines.store') }}" method="POST" class="d-flex gap-2">
                @csrf
                <input type="text" name="name" class="form-control" placeholder="Machine name" required>
                <x-ui.button variant="success" type="submit" icon="ri-add-line" ariaLabel="Add machine">Add</x-ui.button>
            </form>
        </div>
    </div>
</div>
@endsection
```

```php
// routes/web.php — add inside the existing Route::middleware('auth')->group(function () { ... }) block,
// alongside the other permission-gated routes:
    Route::get('machines', [App\Http\Controllers\Job\MachineController::class, 'index'])->name('machines.index')->middleware('permission:jobs.manage-machines');
    Route::post('machines', [App\Http\Controllers\Job\MachineController::class, 'store'])->name('machines.store')->middleware('permission:jobs.manage-machines');
    Route::post('machines/{id}/toggle', [App\Http\Controllers\Job\MachineController::class, 'toggle'])->name('machines.toggle')->middleware('permission:jobs.manage-machines');
```

```blade
{{-- resources/views/layouts/sidebar.blade.php — add near the other @can-gated menu items, e.g. after the products.manage-config block --}}
@can('jobs.manage-machines')
<li class="nav-item">
    <a class="nav-link menu-link" href="{{ route('machines.index') }}">
        <i class="ri-tools-line"></i> <span>Machines</span>
    </a>
</li>
@endcan
```

```php
// app/Providers/RepositoryServiceProvider.php — add import + binding
use App\Repositories\Contracts\MachineRepositoryInterface;
use App\Repositories\Eloquent\EloquentMachineRepository;
// ... inside register():
$this->app->bind(MachineRepositoryInterface::class, EloquentMachineRepository::class);
```

- [ ] **Step 6: Finish the test file (CRUD tests) and run everything**

```php
// tests/Feature/Job/MachineTest.php — append these methods inside the class
    public function test_user_with_permission_can_list_and_create_machines(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $response = $this->actingAs($user)->post(route('machines.store'), ['name' => 'CNC Lathe #3']);

        $response->assertRedirect(route('machines.index'));
        $this->assertDatabaseHas('machines', ['name' => 'CNC Lathe #3', 'is_active' => true]);
    }

    public function test_toggle_flips_active_state(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');
        $machine = Machine::factory()->create(['is_active' => true]);

        $this->actingAs($user)->post(route('machines.toggle', $machine->id));

        $this->assertFalse($machine->fresh()->is_active);
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Worker');

        $response = $this->actingAs($user)->get(route('machines.index'));

        $response->assertForbidden();
    }
```

Run: `php artisan test tests/Feature/Job/MachineTest.php`
Expected: PASS (4/4)

- [ ] **Step 7: Commit**

```bash
git add database/seeders/RolesAndPermissionsSeeder.php app/Repositories/Contracts/MachineRepositoryInterface.php app/Repositories/Eloquent/EloquentMachineRepository.php app/Services/Job/MachineService.php app/Http/Controllers/Job/MachineController.php resources/views/machines/index.blade.php app/Providers/RepositoryServiceProvider.php routes/web.php resources/views/layouts/sidebar.blade.php tests/Feature/Job/MachineTest.php
git commit -m "feat(jobs): add job permissions and machines picklist CRUD"
```

---

### Task 3: Job Audit Logger + Job Repository/Service Foundation

**Files:**
- Create: `app/Services/Job/JobAuditLogger.php`
- Create: `app/Repositories/Contracts/JobRepositoryInterface.php`
- Create: `app/Repositories/Eloquent/EloquentJobRepository.php`
- Modify: `app/Providers/RepositoryServiceProvider.php`
- Test: `tests/Feature/Job/JobAuditLoggerTest.php`

**Interfaces:**
- Produces: `JobAuditLogger::log(Job $job, User $actor, string $action, string $description, array $metadata = []): JobAuditLog` — every later task (creation, approval, status changes, photo upload, completion, reassignment) calls this instead of writing `JobAuditLog::create()` directly, so the write path is centralized in one place.
- Produces: `JobRepositoryInterface` with: `create(array $data): Job`, `find($id): ?Job`, `save(Job $job): Job`, `allForUser(User $user): Collection` (jobs where `created_by` or `assigned_to` matches), `all(): Collection` (every job, for `jobs.view-all`), `pendingApproval(): Collection`, `ownerDashboardStats(): array` (keys: `completed_today`, `pending_approval`, `pending_completion`, `by_worker` — a Collection keyed by worker name to completed-job count).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Job/JobAuditLoggerTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use App\Services\Job\JobAuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobAuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_creates_an_audit_row_with_actor_and_metadata(): void
    {
        $job = Job::factory()->create();
        $actor = User::factory()->create();
        $logger = app(JobAuditLogger::class);

        $entry = $logger->log($job, $actor, 'status_changed', 'Status changed from Assigned to In Progress', [
            'from' => 'assigned',
            'to' => 'in_progress',
        ]);

        $this->assertDatabaseHas('job_audit_logs', [
            'id' => $entry->id,
            'job_id' => $job->id,
            'user_id' => $actor->id,
            'action' => 'status_changed',
        ]);
        $this->assertEquals(['from' => 'assigned', 'to' => 'in_progress'], $entry->fresh()->metadata);
    }
}
```

- [ ] **Step 2: Run it, confirm it fails**

Run: `php artisan test tests/Feature/Job/JobAuditLoggerTest.php`
Expected: FAIL (class `App\Services\Job\JobAuditLogger` not found)

- [ ] **Step 3: Implement**

```php
// app/Services/Job/JobAuditLogger.php
<?php

namespace App\Services\Job;

use App\Models\Job;
use App\Models\JobAuditLog;
use App\Models\User;

class JobAuditLogger
{
    public function log(Job $job, User $actor, string $action, string $description, array $metadata = []): JobAuditLog
    {
        return JobAuditLog::create([
            'job_id' => $job->id,
            'user_id' => $actor->id,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
```

```php
// app/Repositories/Contracts/JobRepositoryInterface.php
<?php

namespace App\Repositories\Contracts;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface JobRepositoryInterface
{
    public function create(array $data): Job;

    public function find($id): ?Job;

    public function save(Job $job): Job;

    public function allForUser(User $user): Collection;

    public function all(): Collection;

    public function pendingApproval(): Collection;

    public function ownerDashboardStats(): array;
}
```

```php
// app/Repositories/Eloquent/EloquentJobRepository.php
<?php

namespace App\Repositories\Eloquent;

use App\Models\Job;
use App\Models\User;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentJobRepository implements JobRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function create(array $data): Job
    {
        return Job::create($data);
    }

    public function find($id): ?Job
    {
        return $this->tenantScope->apply(Job::query())->find($id);
    }

    public function save(Job $job): Job
    {
        $job->save();

        return $job;
    }

    public function allForUser(User $user): Collection
    {
        return $this->tenantScope->apply(
            Job::where('created_by', $user->id)->orWhere('assigned_to', $user->id)
        )->orderBy('id', 'desc')->get();
    }

    public function all(): Collection
    {
        return $this->tenantScope->apply(Job::orderBy('id', 'desc'))->get();
    }

    public function pendingApproval(): Collection
    {
        return $this->tenantScope->apply(Job::where('status', 'pending_approval'))->orderBy('id')->get();
    }

    public function ownerDashboardStats(): array
    {
        $byWorker = $this->tenantScope->apply(
            Job::where('status', 'completed')->whereDate('completed_at', today())
        )->with('assignee')->get()->groupBy('assignee.name')->map->count();

        return [
            'completed_today' => $this->tenantScope->apply(
                Job::where('status', 'completed')->whereDate('completed_at', today())
            )->count(),
            'pending_approval' => $this->tenantScope->apply(Job::where('status', 'pending_approval'))->count(),
            'pending_completion' => $this->tenantScope->apply(
                Job::whereIn('status', ['assigned', 'in_progress', 'on_hold'])
            )->count(),
            'by_worker' => $byWorker,
        ];
    }
}
```

```php
// app/Providers/RepositoryServiceProvider.php — add import + binding
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Repositories\Eloquent\EloquentJobRepository;
// ... inside register():
$this->app->bind(JobRepositoryInterface::class, EloquentJobRepository::class);
```

- [ ] **Step 4: Run the test, confirm it passes**

Run: `php artisan test tests/Feature/Job/JobAuditLoggerTest.php`
Expected: PASS (1/1)

- [ ] **Step 5: Commit**

```bash
git add app/Services/Job/JobAuditLogger.php app/Repositories/Contracts/JobRepositoryInterface.php app/Repositories/Eloquent/EloquentJobRepository.php app/Providers/RepositoryServiceProvider.php tests/Feature/Job/JobAuditLoggerTest.php
git commit -m "feat(jobs): add JobAuditLogger and JobRepository foundation"
```

---

### Task 4: Job Creation (Worker Request + Manager Direct-Assign)

**Files:**
- Create: `app/Services/Job/JobService.php`
- Create: `app/Http/Controllers/Job/JobController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Job/JobCreationTest.php`

**Interfaces:**
- Consumes: `JobRepositoryInterface` (Task 3), `JobAuditLogger` (Task 3).
- Produces: `JobService::createRequest(array $data, User $creator): Job` (always `pending_approval`, `assigned_to = $creator->id`), `JobService::createAssigned(array $data, User $creator): Job` (always `assigned`, requires `assigned_to` in `$data`). Both validate that exactly one of `machine_id`/`site_name` is present, throwing `\InvalidArgumentException` otherwise (caught by the controller as a 422/redirect-back). Later tasks (5, 6, 8, 9) add more methods to this same `JobService` class.

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Job/JobCreationTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Machine;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobCreationTest extends TestCase
{
    use RefreshDatabase;

    private function actingWorker(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Worker');

        return $user;
    }

    private function actingOwner(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        return $user;
    }

    public function test_worker_creating_a_job_lands_in_pending_approval_assigned_to_self(): void
    {
        $worker = $this->actingWorker();
        $machine = Machine::factory()->create();

        $response = $this->actingAs($worker)->post(route('jobs.store'), [
            'title' => 'Fix coolant leak',
            'machine_id' => $machine->id,
            'priority' => 'high',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('jobs', [
            'title' => 'Fix coolant leak',
            'status' => 'pending_approval',
            'created_by' => $worker->id,
            'assigned_to' => $worker->id,
        ]);
        $this->assertDatabaseHas('job_audit_logs', ['action' => 'created']);
    }

    public function test_manager_direct_assign_skips_approval(): void
    {
        $owner = $this->actingOwner();
        $worker = User::factory()->create();
        $machine = Machine::factory()->create();

        $response = $this->actingAs($owner)->post(route('jobs.store'), [
            'title' => 'Recalibrate laser head',
            'machine_id' => $machine->id,
            'priority' => 'medium',
            'assigned_to' => $worker->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('jobs', [
            'title' => 'Recalibrate laser head',
            'status' => 'assigned',
            'assigned_to' => $worker->id,
        ]);
    }

    public function test_job_requires_exactly_one_of_machine_or_site(): void
    {
        $worker = $this->actingWorker();

        $response = $this->actingAs($worker)->postJson(route('jobs.store'), [
            'title' => 'No location given',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('jobs', ['title' => 'No location given']);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Job/JobCreationTest.php`
Expected: FAIL (route `jobs.store` not defined)

- [ ] **Step 3: Implement JobService (creation methods only)**

```php
// app/Services/Job/JobService.php
<?php

namespace App\Services\Job;

use App\Models\Job;
use App\Models\User;
use App\Repositories\Contracts\JobRepositoryInterface;

class JobService
{
    public function __construct(
        private JobRepositoryInterface $repository,
        private JobAuditLogger $auditLogger,
    ) {
    }

    private function assertHasLocation(array $data): void
    {
        $hasMachine = ! empty($data['machine_id']);
        $hasSite = ! empty($data['site_name']);

        if ($hasMachine === $hasSite) {
            throw new \InvalidArgumentException('Job must specify exactly one of machine_id or site_name.');
        }
    }

    public function createRequest(array $data, User $creator): Job
    {
        $this->assertHasLocation($data);

        $job = $this->repository->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'machine_id' => $data['machine_id'] ?? null,
            'site_name' => $data['site_name'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => $data['due_date'] ?? null,
            'created_by' => $creator->id,
            'assigned_to' => $creator->id,
            'status' => 'pending_approval',
        ]);

        $this->auditLogger->log($job, $creator, 'created', "{$creator->name} requested a new job: {$job->title}");

        return $job;
    }

    public function createAssigned(array $data, User $creator): Job
    {
        $this->assertHasLocation($data);

        $job = $this->repository->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'machine_id' => $data['machine_id'] ?? null,
            'site_name' => $data['site_name'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => $data['due_date'] ?? null,
            'created_by' => $creator->id,
            'assigned_to' => $data['assigned_to'],
            'status' => 'assigned',
        ]);

        $this->auditLogger->log($job, $creator, 'created', "{$creator->name} assigned a new job to worker #{$data['assigned_to']}: {$job->title}");

        return $job;
    }
}
```

```php
// app/Http/Controllers/Job/JobController.php
<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Services\Job\JobService;
use App\Services\Job\MachineService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct(
        private JobService $service,
        private MachineService $machineService,
        private JobRepositoryInterface $repository,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $jobs = $user->can('jobs.view-all') ? $this->repository->all() : $this->repository->allForUser($user);

        return view('jobs.index', compact('jobs'));
    }

    public function create(Request $request)
    {
        return view('jobs.create', [
            'machines' => $this->machineService->list(),
            'canAssign' => $request->user()->can('jobs.assign'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'machine_id' => 'nullable|exists:machines,id',
            'site_name' => 'nullable|string|max:255',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        try {
            if ($request->user()->can('jobs.assign') && ! empty($data['assigned_to'])) {
                $job = $this->service->createAssigned($data, $request->user());
            } else {
                $job = $this->service->createRequest($data, $request->user());
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job created.');
    }

    public function show(Request $request, $id)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);
        abort_unless(
            $request->user()->can('jobs.view-all') || $job->created_by === $request->user()->id || $job->assigned_to === $request->user()->id,
            403
        );

        return view('jobs.show', compact('job'));
    }
}
```

```php
// routes/web.php — add inside the existing Route::middleware('auth')->group(function () { ... }) block:
    Route::get('jobs', [App\Http\Controllers\Job\JobController::class, 'index'])->name('jobs.index')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::get('jobs/create', [App\Http\Controllers\Job\JobController::class, 'create'])->name('jobs.create')->middleware('permission:jobs.create|jobs.assign');
    Route::post('jobs', [App\Http\Controllers\Job\JobController::class, 'store'])->name('jobs.store')->middleware('permission:jobs.create|jobs.assign');
    Route::get('jobs/{id}', [App\Http\Controllers\Job\JobController::class, 'show'])->name('jobs.show')->middleware('permission:jobs.view-own|jobs.view-all');
```

- [ ] **Step 4: Run the tests, confirm they pass**

Run: `php artisan test tests/Feature/Job/JobCreationTest.php`
Expected: PASS (3/3)

- [ ] **Step 5: Commit**

```bash
git add app/Services/Job/JobService.php app/Http/Controllers/Job/JobController.php routes/web.php tests/Feature/Job/JobCreationTest.php
git commit -m "feat(jobs): worker job requests and manager direct-assign creation"
```

---

### Task 5: Approval / Rejection

**Files:**
- Modify: `app/Services/Job/JobService.php`
- Modify: `app/Http/Controllers/Job/JobController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Job/JobApprovalTest.php`

**Interfaces:**
- Consumes: `Job` (Task 1), `JobRepositoryInterface`/`JobAuditLogger` (Task 3), `JobService` constructor (Task 4 — add methods to the same class, don't create a second service).
- Produces: `JobService::approve(Job $job, User $manager, ?int $reassignTo = null): Job`, `JobService::reject(Job $job, User $manager, string $reason): Job`. Both throw `\InvalidArgumentException` if `$job->status !== 'pending_approval'`.

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Job/JobApprovalTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_approves_pending_job(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['status' => 'pending_approval']);

        $response = $this->actingAs($owner)->post(route('jobs.approve', $job->id));

        $response->assertRedirect();
        $this->assertEquals('assigned', $job->fresh()->status);
        $this->assertEquals($owner->id, $job->fresh()->decided_by);
        $this->assertNotNull($job->fresh()->decided_at);
        $this->assertDatabaseHas('job_audit_logs', ['job_id' => $job->id, 'action' => 'approved']);
    }

    public function test_manager_rejects_pending_job_with_reason(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['status' => 'pending_approval']);

        $response = $this->actingAs($owner)->post(route('jobs.reject', $job->id), [
            'rejection_reason' => 'Duplicate of job #12',
        ]);

        $response->assertRedirect();
        $this->assertEquals('rejected', $job->fresh()->status);
        $this->assertEquals('Duplicate of job #12', $job->fresh()->rejection_reason);
        $this->assertDatabaseHas('job_audit_logs', ['job_id' => $job->id, 'action' => 'rejected']);
    }

    public function test_rejection_requires_a_reason(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['status' => 'pending_approval']);

        $response = $this->actingAs($owner)->postJson(route('jobs.reject', $job->id), []);

        $response->assertStatus(422);
        $this->assertEquals('pending_approval', $job->fresh()->status);
    }

    public function test_worker_cannot_approve(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->assignRole('Worker');
        $job = Job::factory()->create(['status' => 'pending_approval']);

        $response = $this->actingAs($worker)->post(route('jobs.approve', $job->id));

        $response->assertForbidden();
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Job/JobApprovalTest.php`
Expected: FAIL (route `jobs.approve` not defined)

- [ ] **Step 3: Add approve/reject to JobService, controller actions, routes**

```php
// app/Services/Job/JobService.php — add these two methods inside the class
    public function approve(Job $job, User $manager, ?int $reassignTo = null): Job
    {
        if ($job->status !== 'pending_approval') {
            throw new \InvalidArgumentException('Only a pending-approval job can be approved.');
        }

        $job->status = 'assigned';
        $job->decided_by = $manager->id;
        $job->decided_at = now();
        if ($reassignTo) {
            $job->assigned_to = $reassignTo;
        }
        $this->repository->save($job);

        $this->auditLogger->log($job, $manager, 'approved', "{$manager->name} approved the job request.");

        return $job;
    }

    public function reject(Job $job, User $manager, string $reason): Job
    {
        if ($job->status !== 'pending_approval') {
            throw new \InvalidArgumentException('Only a pending-approval job can be rejected.');
        }

        $job->status = 'rejected';
        $job->decided_by = $manager->id;
        $job->decided_at = now();
        $job->rejection_reason = $reason;
        $this->repository->save($job);

        $this->auditLogger->log($job, $manager, 'rejected', "{$manager->name} rejected the job request: {$reason}");

        return $job;
    }
```

```php
// app/Http/Controllers/Job/JobController.php — add these two methods inside the class
    public function approve(Request $request, $id)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->approve($job, $request->user(), $request->input('assigned_to'));
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('jobs.pending-approval')->with('success', 'Job approved.');
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->reject($job, $request->user(), $data['rejection_reason']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('jobs.pending-approval')->with('success', 'Job rejected.');
    }

    public function pendingApproval()
    {
        return view('jobs.pending-approval', ['jobs' => $this->repository->pendingApproval()]);
    }
```

```php
// routes/web.php — add inside the same auth group:
    Route::get('jobs-pending-approval', [App\Http\Controllers\Job\JobController::class, 'pendingApproval'])->name('jobs.pending-approval')->middleware('permission:jobs.approve');
    Route::post('jobs/{id}/approve', [App\Http\Controllers\Job\JobController::class, 'approve'])->name('jobs.approve')->middleware('permission:jobs.approve');
    Route::post('jobs/{id}/reject', [App\Http\Controllers\Job\JobController::class, 'reject'])->name('jobs.reject')->middleware('permission:jobs.approve');
```

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Job/JobApprovalTest.php`
Expected: PASS (4/4)

- [ ] **Step 5: Commit**

```bash
git add app/Services/Job/JobService.php app/Http/Controllers/Job/JobController.php routes/web.php tests/Feature/Job/JobApprovalTest.php
git commit -m "feat(jobs): manager approve/reject flow for pending job requests"
```

---

### Task 6: Status Transitions — Start / Hold / Resume

**Files:**
- Modify: `app/Services/Job/JobService.php`
- Modify: `app/Http/Controllers/Job/JobController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Job/JobStatusTransitionTest.php`

**Interfaces:**
- Produces: `JobService::start(Job $job, User $actor): Job` (`assigned` → `in_progress`), `JobService::hold(Job $job, User $actor, string $reason): Job` (`in_progress` → `on_hold`), `JobService::resume(Job $job, User $actor): Job` (`on_hold` → `in_progress`). Each throws `\InvalidArgumentException` on an invalid source status, and `\Illuminate\Auth\Access\AuthorizationException` if `$actor->id !== $job->assigned_to` and the actor lacks `jobs.assign`.

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Job/JobStatusTransitionTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    private function assignedWorker(): array
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->assignRole('Worker');
        $job = Job::factory()->create(['status' => 'assigned', 'assigned_to' => $worker->id]);

        return [$worker, $job];
    }

    public function test_assigned_worker_can_start_job(): void
    {
        [$worker, $job] = $this->assignedWorker();

        $response = $this->actingAs($worker)->post(route('jobs.start', $job->id));

        $response->assertRedirect();
        $this->assertEquals('in_progress', $job->fresh()->status);
        $this->assertDatabaseHas('job_audit_logs', ['job_id' => $job->id, 'action' => 'status_changed']);
    }

    public function test_other_worker_cannot_start_someone_elses_job(): void
    {
        [, $job] = $this->assignedWorker();
        $otherWorker = User::factory()->create();
        $otherWorker->assignRole('Worker');

        $response = $this->actingAs($otherWorker)->post(route('jobs.start', $job->id));

        $response->assertForbidden();
    }

    public function test_hold_requires_reason_and_moves_from_in_progress(): void
    {
        [$worker, $job] = $this->assignedWorker();
        $job->update(['status' => 'in_progress']);

        $response = $this->actingAs($worker)->post(route('jobs.hold', $job->id), ['on_hold_reason' => 'Waiting for replacement part']);

        $response->assertRedirect();
        $this->assertEquals('on_hold', $job->fresh()->status);
        $this->assertEquals('Waiting for replacement part', $job->fresh()->on_hold_reason);
    }

    public function test_hold_without_reason_fails(): void
    {
        [$worker, $job] = $this->assignedWorker();
        $job->update(['status' => 'in_progress']);

        $response = $this->actingAs($worker)->postJson(route('jobs.hold', $job->id), []);

        $response->assertStatus(422);
    }

    public function test_resume_moves_on_hold_back_to_in_progress(): void
    {
        [$worker, $job] = $this->assignedWorker();
        $job->update(['status' => 'on_hold', 'on_hold_reason' => 'parts']);

        $response = $this->actingAs($worker)->post(route('jobs.resume', $job->id));

        $response->assertRedirect();
        $this->assertEquals('in_progress', $job->fresh()->status);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Job/JobStatusTransitionTest.php`
Expected: FAIL (route `jobs.start` not defined)

- [ ] **Step 3: Implement**

```php
// app/Services/Job/JobService.php — add inside the class
    private function assertActorCanAct(Job $job, User $actor): void
    {
        if ($actor->id !== $job->assigned_to && ! $actor->can('jobs.assign')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('You are not assigned to this job.');
        }
    }

    public function start(Job $job, User $actor): Job
    {
        $this->assertActorCanAct($job, $actor);
        if ($job->status !== 'assigned') {
            throw new \InvalidArgumentException('Only an assigned job can be started.');
        }

        $job->status = 'in_progress';
        $this->repository->save($job);
        $this->auditLogger->log($job, $actor, 'status_changed', 'Status changed from Assigned to In Progress', ['from' => 'assigned', 'to' => 'in_progress']);

        return $job;
    }

    public function hold(Job $job, User $actor, string $reason): Job
    {
        $this->assertActorCanAct($job, $actor);
        if ($job->status !== 'in_progress') {
            throw new \InvalidArgumentException('Only an in-progress job can be put on hold.');
        }

        $job->status = 'on_hold';
        $job->on_hold_reason = $reason;
        $this->repository->save($job);
        $this->auditLogger->log($job, $actor, 'on_hold', "Job put on hold: {$reason}", ['from' => 'in_progress', 'to' => 'on_hold']);

        return $job;
    }

    public function resume(Job $job, User $actor): Job
    {
        $this->assertActorCanAct($job, $actor);
        if ($job->status !== 'on_hold') {
            throw new \InvalidArgumentException('Only an on-hold job can be resumed.');
        }

        $job->status = 'in_progress';
        $this->repository->save($job);
        $this->auditLogger->log($job, $actor, 'resumed', 'Job resumed from hold', ['from' => 'on_hold', 'to' => 'in_progress']);

        return $job;
    }
```

```php
// app/Http/Controllers/Job/JobController.php — add inside the class
    public function start(Request $request, $id)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->start($job, $request->user());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job started.');
    }

    public function hold(Request $request, $id)
    {
        $data = $request->validate(['on_hold_reason' => 'required|string|max:1000']);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->hold($job, $request->user(), $data['on_hold_reason']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job put on hold.');
    }

    public function resume(Request $request, $id)
    {
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->resume($job, $request->user());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job resumed.');
    }
```

```php
// routes/web.php — add inside the same auth group:
    Route::post('jobs/{id}/start', [App\Http\Controllers\Job\JobController::class, 'start'])->name('jobs.start')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/hold', [App\Http\Controllers\Job\JobController::class, 'hold'])->name('jobs.hold')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/resume', [App\Http\Controllers\Job\JobController::class, 'resume'])->name('jobs.resume')->middleware('permission:jobs.view-own|jobs.view-all');
```

Note: route-level `permission:` middleware only checks the role has the permission at all (so any Worker with `jobs.view-own` passes it) — the real per-record ownership check (`assigned_to === actor` or `jobs.assign`) happens in `JobService::assertActorCanAct()`, which is the correct layer for a record-level rule per `DEVELOPMENT-STANDARDS.md` (repository/service owns business logic, not route middleware).

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Job/JobStatusTransitionTest.php`
Expected: PASS (5/5)

- [ ] **Step 5: Commit**

```bash
git add app/Services/Job/JobService.php app/Http/Controllers/Job/JobController.php routes/web.php tests/Feature/Job/JobStatusTransitionTest.php
git commit -m "feat(jobs): start/hold/resume status transitions with ownership checks"
```

---

### Task 7: Photo Upload + GPS + Compression + Reverse Geocoding

**Files:**
- Create: `app/Support/ImageCompressor.php`
- Create: `app/Support/Geocoding/NominatimGeocoder.php`
- Create: `app/Repositories/Contracts/JobPhotoRepositoryInterface.php`
- Create: `app/Repositories/Eloquent/EloquentJobPhotoRepository.php`
- Create: `app/Services/Job/JobPhotoService.php`
- Modify: `app/Providers/RepositoryServiceProvider.php`
- Modify: `app/Http/Controllers/Job/JobController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Job/JobPhotoUploadTest.php`

**Interfaces:**
- Produces: `ImageCompressor::compress(\Illuminate\Http\UploadedFile $file, string $destinationDiskPath, int $maxDimension = 1600, int $quality = 75): void` — writes a resized/re-encoded JPEG to `storage/app/public/{$destinationDiskPath}` using GD only.
- Produces: `NominatimGeocoder::reverseGeocode(float $lat, float $lng): ?string` — returns a human-readable address or `null` on any failure/timeout (never throws).
- Produces: `JobPhotoService::upload(Job $job, User $uploader, \Illuminate\Http\UploadedFile $file, ?float $lat, ?float $lng): JobPhoto`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Job/JobPhotoUploadTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_uploads_photo_with_gps_and_gets_map_link(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->assignRole('Worker');
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);
        $file = UploadedFile::fake()->image('proof.jpg', 2000, 1500);

        $response = $this->actingAs($worker)->post(route('jobs.photos.store', $job->id), [
            'photo' => $file,
            'latitude' => '23.0225000',
            'longitude' => '72.5714000',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_photos', [
            'job_id' => $job->id,
            'uploaded_by' => $worker->id,
            'location_captured' => true,
        ]);
        $photo = $job->photos()->first();
        $this->assertStringContainsString('23.0225', $photo->map_link);
        $this->assertDatabaseHas('job_audit_logs', ['job_id' => $job->id, 'action' => 'photo_uploaded']);
    }

    public function test_upload_without_gps_is_flagged_not_silently_dropped(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->assignRole('Worker');
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);
        $file = UploadedFile::fake()->image('proof.jpg', 800, 600);

        $response = $this->actingAs($worker)->post(route('jobs.photos.store', $job->id), ['photo' => $file]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_photos', ['job_id' => $job->id, 'location_captured' => false]);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Job/JobPhotoUploadTest.php`
Expected: FAIL (route `jobs.photos.store` not defined)

- [ ] **Step 3: Implement**

```php
// app/Support/ImageCompressor.php
<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageCompressor
{
    public function compress(UploadedFile $file, string $destinationDiskPath, int $maxDimension = 1600, int $quality = 75): void
    {
        $image = $this->readImage($file->getRealPath(), $file->getMimeType());

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, $maxDimension / max($width, $height));

        if ($scale < 1) {
            $newWidth = (int) round($width * $scale);
            $newHeight = (int) round($height * $scale);
            $resized = imagescale($image, $newWidth, $newHeight);
            imagedestroy($image);
            $image = $resized;
        }

        ob_start();
        imagejpeg($image, null, $quality);
        $contents = ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($destinationDiskPath, $contents);
    }

    private function readImage(string $path, ?string $mimeType)
    {
        return match ($mimeType) {
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => imagecreatefromjpeg($path),
        };
    }
}
```

```php
// app/Support/Geocoding/NominatimGeocoder.php
<?php

namespace App\Support\Geocoding;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class NominatimGeocoder
{
    public function __construct(private ?Client $client = null)
    {
        $this->client ??= new Client(['timeout' => 3]);
    }

    public function reverseGeocode(float $lat, float $lng): ?string
    {
        try {
            $response = $this->client->get('https://nominatim.openstreetmap.org/reverse', [
                'query' => ['lat' => $lat, 'lon' => $lng, 'format' => 'json'],
                'headers' => ['User-Agent' => config('app.name') . ' JobTracking/1.0'],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            return $data['display_name'] ?? null;
        } catch (\Throwable $e) {
            Log::warning('Reverse geocode failed', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
```

```php
// app/Repositories/Contracts/JobPhotoRepositoryInterface.php
<?php

namespace App\Repositories\Contracts;

use App\Models\JobPhoto;

interface JobPhotoRepositoryInterface
{
    public function create(array $data): JobPhoto;
}
```

```php
// app/Repositories/Eloquent/EloquentJobPhotoRepository.php
<?php

namespace App\Repositories\Eloquent;

use App\Models\JobPhoto;
use App\Repositories\Contracts\JobPhotoRepositoryInterface;

class EloquentJobPhotoRepository implements JobPhotoRepositoryInterface
{
    public function create(array $data): JobPhoto
    {
        return JobPhoto::create($data);
    }
}
```

```php
// app/Services/Job/JobPhotoService.php
<?php

namespace App\Services\Job;

use App\Models\Job;
use App\Models\JobPhoto;
use App\Models\User;
use App\Repositories\Contracts\JobPhotoRepositoryInterface;
use App\Support\Geocoding\NominatimGeocoder;
use App\Support\ImageCompressor;
use Illuminate\Http\UploadedFile;

class JobPhotoService
{
    public function __construct(
        private JobPhotoRepositoryInterface $repository,
        private JobAuditLogger $auditLogger,
        private ImageCompressor $compressor,
        private NominatimGeocoder $geocoder,
    ) {
    }

    public function upload(Job $job, User $uploader, UploadedFile $file, ?float $lat, ?float $lng): JobPhoto
    {
        $diskPath = 'job-photos/' . $job->id . '/' . uniqid('photo_', true) . '.jpg';
        $this->compressor->compress($file, $diskPath);

        $locationCaptured = $lat !== null && $lng !== null;
        $mapLink = $locationCaptured ? "https://www.google.com/maps?q={$lat},{$lng}" : null;
        $address = $locationCaptured ? $this->geocoder->reverseGeocode($lat, $lng) : null;

        $photo = $this->repository->create([
            'job_id' => $job->id,
            'uploaded_by' => $uploader->id,
            'path' => $diskPath,
            'latitude' => $lat,
            'longitude' => $lng,
            'location_captured' => $locationCaptured,
            'map_link' => $mapLink,
            'address' => $address,
            'captured_at' => now(),
        ]);

        $description = $locationCaptured
            ? "{$uploader->name} uploaded a proof photo (location captured)."
            : "{$uploader->name} uploaded a proof photo (location NOT captured).";
        $this->auditLogger->log($job, $uploader, 'photo_uploaded', $description);

        return $photo;
    }
}
```

```php
// app/Providers/RepositoryServiceProvider.php — add import + binding
use App\Repositories\Contracts\JobPhotoRepositoryInterface;
use App\Repositories\Eloquent\EloquentJobPhotoRepository;
// ... inside register():
$this->app->bind(JobPhotoRepositoryInterface::class, EloquentJobPhotoRepository::class);
```

```php
// app/Http/Controllers/Job/JobController.php — add constructor param + method
// constructor becomes:
    public function __construct(
        private JobService $service,
        private MachineService $machineService,
        private JobRepositoryInterface $repository,
        private \App\Services\Job\JobPhotoService $photoService,
    ) {
    }

// new method inside the class:
    public function storePhoto(Request $request, $id)
    {
        $data = $request->validate([
            'photo' => 'required|image|max:10240',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);
        abort_unless($job->assigned_to === $request->user()->id || $request->user()->can('jobs.assign'), 403);

        $this->photoService->upload(
            $job,
            $request->user(),
            $request->file('photo'),
            isset($data['latitude']) ? (float) $data['latitude'] : null,
            isset($data['longitude']) ? (float) $data['longitude'] : null,
        );

        return redirect()->route('jobs.show', $job->id)->with('success', 'Photo uploaded.');
    }
```

```php
// routes/web.php — add inside the same auth group:
    Route::post('jobs/{id}/photos', [App\Http\Controllers\Job\JobController::class, 'storePhoto'])->name('jobs.photos.store')->middleware('permission:jobs.view-own|jobs.view-all');
```

Note: `storage/app/public` must be symlinked (`php artisan storage:link`) for uploaded photos to be publicly browsable — this app currently stores files directly under `public/` (see `App\Traits\HandlesAvatarUpload`) and has never run `storage:link`. Run it once as part of this task's manual verification; it's an idempotent, non-destructive artisan command safe to run against the live server.

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Job/JobPhotoUploadTest.php`
Expected: PASS (2/2)

- [ ] **Step 5: Commit**

```bash
git add app/Support/ImageCompressor.php app/Support/Geocoding/NominatimGeocoder.php app/Repositories/Contracts/JobPhotoRepositoryInterface.php app/Repositories/Eloquent/EloquentJobPhotoRepository.php app/Services/Job/JobPhotoService.php app/Providers/RepositoryServiceProvider.php app/Http/Controllers/Job/JobController.php routes/web.php tests/Feature/Job/JobPhotoUploadTest.php
git commit -m "feat(jobs): photo upload with GPS capture, GD compression, and reverse geocoding"
```

---

### Task 8: Job Completion

**Files:**
- Modify: `app/Services/Job/JobService.php`
- Modify: `app/Http/Controllers/Job/JobController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Job/JobCompletionTest.php`

**Interfaces:**
- Produces: `JobService::complete(Job $job, User $actor, string $notes): Job` — requires `$job->status === 'in_progress'` and `$job->photos()->exists()` (at least one photo already uploaded via Task 7's endpoint before this is called), otherwise throws `\InvalidArgumentException`. Sets `completed_at = now()` (server clock, never client-supplied).

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Job/JobCompletionTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\JobPhoto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobCompletionTest extends TestCase
{
    use RefreshDatabase;

    private function inProgressJobWithPhoto(): array
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->assignRole('Worker');
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);
        JobPhoto::create([
            'job_id' => $job->id,
            'uploaded_by' => $worker->id,
            'path' => 'job-photos/test.jpg',
            'captured_at' => now(),
        ]);

        return [$worker, $job];
    }

    public function test_complete_requires_notes_and_at_least_one_photo(): void
    {
        [$worker, $job] = $this->inProgressJobWithPhoto();

        $response = $this->actingAs($worker)->post(route('jobs.complete', $job->id), [
            'completion_notes' => 'Replaced the coolant hose and tested at full RPM.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('completed', $job->fresh()->status);
        $this->assertNotNull($job->fresh()->completed_at);
        $this->assertDatabaseHas('job_audit_logs', ['job_id' => $job->id, 'action' => 'completed']);
    }

    public function test_complete_fails_without_any_photo(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->assignRole('Worker');
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $worker->id]);

        $response = $this->actingAs($worker)->postJson(route('jobs.complete', $job->id), [
            'completion_notes' => 'Done',
        ]);

        $response->assertStatus(422);
        $this->assertEquals('in_progress', $job->fresh()->status);
    }

    public function test_complete_fails_without_notes(): void
    {
        [$worker, $job] = $this->inProgressJobWithPhoto();

        $response = $this->actingAs($worker)->postJson(route('jobs.complete', $job->id), []);

        $response->assertStatus(422);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Job/JobCompletionTest.php`
Expected: FAIL (route `jobs.complete` not defined)

- [ ] **Step 3: Implement**

```php
// app/Services/Job/JobService.php — add inside the class
    public function complete(Job $job, User $actor, string $notes): Job
    {
        $this->assertActorCanAct($job, $actor);
        if ($job->status !== 'in_progress') {
            throw new \InvalidArgumentException('Only an in-progress job can be completed.');
        }
        if (! $job->photos()->exists()) {
            throw new \InvalidArgumentException('At least one proof photo is required to complete a job.');
        }

        $job->status = 'completed';
        $job->completion_notes = $notes;
        $job->completed_at = now();
        $this->repository->save($job);

        $this->auditLogger->log($job, $actor, 'completed', "{$actor->name} marked the job complete.", ['from' => 'in_progress', 'to' => 'completed']);

        return $job;
    }
```

```php
// app/Http/Controllers/Job/JobController.php — add inside the class
    public function complete(Request $request, $id)
    {
        $data = $request->validate(['completion_notes' => 'required|string|max:2000']);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->complete($job, $request->user(), $data['completion_notes']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job marked complete.');
    }
```

Note: the "no photo" failure path returns JSON 422 (matching the test's `postJson` expectation and the existing `InvoiceController`/`invoice.store` convention of JSON validation errors for AJAX-style submissions) rather than `back()->withErrors()`, since the worker UI (Task 12) submits completion via `fetch`/AJAX so it can show the GPS-status indicator inline without a full page reload.

```php
// routes/web.php — add inside the same auth group:
    Route::post('jobs/{id}/complete', [App\Http\Controllers\Job\JobController::class, 'complete'])->name('jobs.complete')->middleware('permission:jobs.view-own|jobs.view-all');
```

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Job/JobCompletionTest.php`
Expected: PASS (3/3)

- [ ] **Step 5: Commit**

```bash
git add app/Services/Job/JobService.php app/Http/Controllers/Job/JobController.php routes/web.php tests/Feature/Job/JobCompletionTest.php
git commit -m "feat(jobs): job completion requiring notes and at least one proof photo"
```

---

### Task 9: Reassignment

**Files:**
- Modify: `app/Services/Job/JobService.php`
- Modify: `app/Http/Controllers/Job/JobController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Job/JobReassignmentTest.php`

**Interfaces:**
- Produces: `JobService::reassign(Job $job, User $manager, int $newAssigneeId): Job` — allowed from `assigned`, `in_progress`, or `on_hold` (throws `\InvalidArgumentException` from `pending_approval`, `completed`, or `rejected`). Status is unchanged.

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Job/JobReassignmentTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobReassignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_reassigns_an_in_progress_job(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $oldWorker = User::factory()->create();
        $newWorker = User::factory()->create();
        $job = Job::factory()->create(['status' => 'in_progress', 'assigned_to' => $oldWorker->id]);

        $response = $this->actingAs($owner)->post(route('jobs.reassign', $job->id), ['assigned_to' => $newWorker->id]);

        $response->assertRedirect();
        $this->assertEquals($newWorker->id, $job->fresh()->assigned_to);
        $this->assertEquals('in_progress', $job->fresh()->status);
        $this->assertDatabaseHas('job_audit_logs', ['job_id' => $job->id, 'action' => 'reassigned']);
    }

    public function test_cannot_reassign_a_completed_job(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $newWorker = User::factory()->create();
        $job = Job::factory()->create(['status' => 'completed']);

        $response = $this->actingAs($owner)->postJson(route('jobs.reassign', $job->id), ['assigned_to' => $newWorker->id]);

        $response->assertStatus(422);
    }

    public function test_worker_cannot_reassign(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->assignRole('Worker');
        $job = Job::factory()->create(['status' => 'assigned', 'assigned_to' => $worker->id]);
        $newWorker = User::factory()->create();

        $response = $this->actingAs($worker)->post(route('jobs.reassign', $job->id), ['assigned_to' => $newWorker->id]);

        $response->assertForbidden();
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Job/JobReassignmentTest.php`
Expected: FAIL (route `jobs.reassign` not defined)

- [ ] **Step 3: Implement**

```php
// app/Services/Job/JobService.php — add inside the class
    public function reassign(Job $job, User $manager, int $newAssigneeId): Job
    {
        if (! in_array($job->status, ['assigned', 'in_progress', 'on_hold'], true)) {
            throw new \InvalidArgumentException('Only an assigned, in-progress, or on-hold job can be reassigned.');
        }

        $previousAssignee = $job->assigned_to;
        $job->assigned_to = $newAssigneeId;
        $this->repository->save($job);

        $this->auditLogger->log($job, $manager, 'reassigned', "{$manager->name} reassigned the job.", [
            'from_user_id' => $previousAssignee,
            'to_user_id' => $newAssigneeId,
        ]);

        return $job;
    }
```

```php
// app/Http/Controllers/Job/JobController.php — add inside the class
    public function reassign(Request $request, $id)
    {
        $data = $request->validate(['assigned_to' => 'required|exists:users,id']);
        $job = $this->repository->find($id);
        abort_if(! $job, 404);

        try {
            $this->service->reassign($job, $request->user(), (int) $data['assigned_to']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return redirect()->route('jobs.show', $job->id)->with('success', 'Job reassigned.');
    }
```

```php
// routes/web.php — add inside the same auth group:
    Route::post('jobs/{id}/reassign', [App\Http\Controllers\Job\JobController::class, 'reassign'])->name('jobs.reassign')->middleware('permission:jobs.assign');
```

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Job/JobReassignmentTest.php`
Expected: PASS (3/3)

- [ ] **Step 5: Commit**

```bash
git add app/Services/Job/JobService.php app/Http/Controllers/Job/JobController.php routes/web.php tests/Feature/Job/JobReassignmentTest.php
git commit -m "feat(jobs): manager reassignment of active jobs"
```

---

### Task 10: In-App Bell Notifications

**Files:**
- Create: `database/migrations/2026_07_18_000006_create_notifications_table.php`
- Create: `app/Notifications/JobDecisionNotification.php`
- Modify: `app/Services/Job/JobService.php` (fire the notification from `approve()`/`reject()`)
- Create: `app/Providers/ViewComposerServiceProvider.php`
- Modify: `config/app.php` (register the new provider)
- Modify: `resources/views/layouts/topbar.blade.php`
- Test: `tests/Feature/Job/JobNotificationTest.php`

**Interfaces:**
- Produces: `JobDecisionNotification` (Laravel `Notification`, `via()` returns `['database']` in this task — Task 11 adds the `webpush` channel to the same class), constructed as `new JobDecisionNotification(Job $job, string $decision)` where `$decision` is `'approved'` or `'rejected'`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Job/JobNotificationTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use App\Notifications\JobDecisionNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class JobNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_a_job_notifies_the_assigned_worker(): void
    {
        Notification::fake();
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $worker = User::factory()->create();
        $job = Job::factory()->create(['status' => 'pending_approval', 'created_by' => $worker->id, 'assigned_to' => $worker->id]);

        $this->actingAs($owner)->post(route('jobs.approve', $job->id));

        Notification::assertSentTo($worker, JobDecisionNotification::class, function ($notification) {
            return $notification->decision === 'approved';
        });
    }

    public function test_rejecting_a_job_notifies_the_creator_with_reason_in_database_payload(): void
    {
        Notification::fake();
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $worker = User::factory()->create();
        $job = Job::factory()->create(['status' => 'pending_approval', 'created_by' => $worker->id, 'assigned_to' => $worker->id]);

        $this->actingAs($owner)->post(route('jobs.reject', $job->id), ['rejection_reason' => 'Not needed']);

        Notification::assertSentTo($worker, JobDecisionNotification::class, function ($notification) {
            return $notification->decision === 'rejected';
        });
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Job/JobNotificationTest.php`
Expected: FAIL (class `App\Notifications\JobDecisionNotification` not found)

- [ ] **Step 3: Implement**

```php
// database/migrations/2026_07_18_000006_create_notifications_table.php
// Standard Laravel notifications table (this app has never used
// Illuminate\Notifications before, despite User already using the
// Notifiable trait — no migration for it existed until now).
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
```

```php
// app/Notifications/JobDecisionNotification.php
<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Notifications\Notification;

class JobDecisionNotification extends Notification
{
    public function __construct(public Job $job, public string $decision)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'job_id' => $this->job->id,
            'job_title' => $this->job->title,
            'decision' => $this->decision,
            'reason' => $this->decision === 'rejected' ? $this->job->rejection_reason : null,
        ];
    }
}
```

```php
// app/Services/Job/JobService.php — add the import at the top:
use App\Notifications\JobDecisionNotification;

// then in approve(), right before "return $job;":
        $job->assignee?->notify(new JobDecisionNotification($job, 'approved'));

// and in reject(), right before "return $job;":
        $job->assignee?->notify(new JobDecisionNotification($job, 'rejected'));
```

```php
// app/Providers/ViewComposerServiceProvider.php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('layouts.topbar', function ($view) {
            $user = auth()->user();

            $view->with('jobNotifications', $user
                ? $user->notifications()->latest()->limit(10)->get()
                : collect());
            $view->with('unreadJobNotificationCount', $user ? $user->unreadNotifications()->count() : 0);
        });
    }
}
```

```php
// config/app.php — add to the 'providers' array, after App\Providers\RepositoryServiceProvider::class,
        App\Providers\ViewComposerServiceProvider::class,
```

- [ ] **Step 4: Wire the topbar bell to real data**

Read `resources/views/layouts/topbar.blade.php` around lines 375-490 first (the notification dropdown markup) before editing — replace only the repeated demo `notification-item` blocks (the "Angela Bernier" placeholders) with a `@forelse($jobNotifications as $notification)` loop rendering `$notification->data['job_title']`, `$notification->data['decision']`, and `$notification->data['reason']`, each wrapped in a link to `route('jobs.show', $notification->data['job_id'])`. Preserve the surrounding dropdown/tab DOM structure (`notification-list`, `dropdown-menu-lg`, tab markup) exactly — this is a data swap, not a redesign. Use `<x-ui.empty-state icon="ri-notification-off-line" message="No notifications yet." />` for the `@empty` branch. Update the bell badge count element to show `$unreadJobNotificationCount` instead of any hardcoded number.

- [ ] **Step 5: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Job/JobNotificationTest.php`
Expected: PASS (2/2)

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_07_18_000006_create_notifications_table.php app/Notifications/JobDecisionNotification.php app/Services/Job/JobService.php app/Providers/ViewComposerServiceProvider.php config/app.php resources/views/layouts/topbar.blade.php tests/Feature/Job/JobNotificationTest.php
git commit -m "feat(jobs): in-app bell notifications for job approval/rejection"
```

---

### Task 11: Web Push Notifications

**Files:**
- Modify: `composer.json` (require `laravel-notification-channels/webpush`)
- Modify: `app/Models/User.php` (add `HasPushSubscriptions` trait)
- Modify: `app/Notifications/JobDecisionNotification.php` (add `webpush` channel + `toWebPush()`)
- Create: `public/sw.js`
- Create: `public/build/js/push-subscribe.js`
- Modify: `resources/views/layouts/topbar.blade.php` (subscribe prompt)
- Modify: `.env.example` (VAPID key placeholders — never commit real keys)
- Create: `app/Http/Controllers/Job/PushSubscriptionController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Job/PushSubscriptionTest.php`

**Interfaces:**
- Consumes: `push_subscriptions` table (already migrated in Task 1 — matches the exact schema `laravel-notification-channels/webpush` expects, so its own migration publish step is unnecessary).

- [ ] **Step 1: Install the package**

Run: `composer require laravel-notification-channels/webpush`
Expected: package added to `composer.json`/`composer.lock`, `NotificationChannels\WebPush\WebPushServiceProvider` auto-discovered.

- [ ] **Step 2: Generate VAPID keys**

Run: `php artisan webpush:vapid`
Expected: prints a public/private keypair and appends `VAPID_PUBLIC_KEY` / `VAPID_PRIVATE_KEY` to `.env`. Add matching blank placeholders to `.env.example`:

```
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
```

- [ ] **Step 3: Write the failing test**

```php
// tests/Feature/Job/PushSubscriptionTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_a_push_subscription(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('push-subscriptions.store'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
            'publicKey' => 'test-public-key',
            'authToken' => 'test-auth-token',
            'contentEncoding' => 'aesgcm',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id' => $user->id,
            'subscribable_type' => User::class,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        ]);
    }
}
```

- [ ] **Step 4: Run, confirm failure**

Run: `php artisan test tests/Feature/Job/PushSubscriptionTest.php`
Expected: FAIL (route `push-subscriptions.store` not defined)

- [ ] **Step 5: Implement**

```php
// app/Models/User.php — add import + trait
use NotificationChannels\WebPush\HasPushSubscriptions;
// ... in the class:
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasPushSubscriptions;
```

```php
// app/Notifications/JobDecisionNotification.php — updated via() and new toWebPush()
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

    public function via($notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $title = $this->decision === 'approved' ? 'Job Approved' : 'Job Rejected';
        $body = $this->decision === 'approved'
            ? "Your job \"{$this->job->title}\" was approved."
            : "Your job \"{$this->job->title}\" was rejected: {$this->job->rejection_reason}";

        return (new WebPushMessage())
            ->title($title)
            ->icon('/favicon.ico')
            ->body($body)
            ->action('View Job', 'view_job')
            ->data(['job_id' => $this->job->id]);
    }
```

```php
// app/Http/Controllers/Job/PushSubscriptionController.php
<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint' => 'required|string',
            'publicKey' => 'nullable|string',
            'authToken' => 'nullable|string',
            'contentEncoding' => 'nullable|string',
        ]);

        $request->user()->updatePushSubscription(
            $data['endpoint'],
            $data['publicKey'] ?? null,
            $data['authToken'] ?? null,
            $data['contentEncoding'] ?? null,
        );

        return response()->json(['success' => true]);
    }
}
```

```php
// routes/web.php — add inside the auth group:
    Route::post('push-subscriptions', [App\Http\Controllers\Job\PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
```

```js
// public/sw.js
self.addEventListener('push', function (event) {
    const data = event.data ? event.data.json() : {};
    event.waitUntil(
        self.registration.showNotification(data.title || 'Notification', {
            body: data.body || '',
            icon: data.icon || '/favicon.ico',
        })
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const jobId = event.notification.data && event.notification.data.job_id;
    if (jobId) {
        event.waitUntil(clients.openWindow('/jobs/' + jobId));
    }
});
```

```js
// public/build/js/push-subscribe.js
// Requests notification permission and registers the service worker + push
// subscription. Loaded only on pages behind auth (topbar include), guarded
// so it degrades silently on unsupported browsers instead of throwing.
(function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        return;
    }

    const banner = document.getElementById('push-subscribe-banner');

    function subscribe(vapidPublicKey) {
        navigator.serviceWorker.register('/sw.js').then(function (registration) {
            return registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: vapidPublicKey,
            });
        }).then(function (subscription) {
            const key = subscription.getKey('p256dh');
            const token = subscription.getKey('auth');
            return fetch('/push-subscriptions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    publicKey: key ? btoa(String.fromCharCode(...new Uint8Array(key))) : null,
                    authToken: token ? btoa(String.fromCharCode(...new Uint8Array(token))) : null,
                    contentEncoding: 'aesgcm',
                }),
            });
        }).then(function () {
            if (banner) banner.remove();
        }).catch(function () {
            // Silently degrade — the in-app bell remains the reliable channel.
        });
    }

    if (banner) {
        banner.querySelector('[data-push-enable]').addEventListener('click', function () {
            Notification.requestPermission().then(function (permission) {
                if (permission === 'granted') {
                    subscribe(banner.dataset.vapidKey);
                } else {
                    banner.remove();
                }
            });
        });
        banner.querySelector('[data-push-dismiss]').addEventListener('click', function () {
            banner.remove();
        });
    }
})();
```

```blade
{{-- resources/views/layouts/topbar.blade.php — add near the top of the topbar, only for authenticated users, once (a dismissible banner, not a modal — doesn't block page use) --}}
@auth
<div id="push-subscribe-banner" data-vapid-key="{{ config('webpush.vapid.public_key') }}" class="alert alert-info d-flex justify-content-between align-items-center m-0 rounded-0" style="display:none">
    <span><i class="ri-notification-3-line"></i> Enable notifications for job updates?</span>
    <span>
        <x-ui.button variant="primary" size="sm" data-push-enable type="button">Enable</x-ui.button>
        <x-ui.button variant="secondary" size="sm" data-push-dismiss type="button" icon="ri-close-line" ariaLabel="Dismiss">Not now</x-ui.button>
    </span>
</div>
<script>
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
        var banner = document.getElementById('push-subscribe-banner');
        if (banner) { banner.style.display = 'flex'; }
    }
</script>
<script src="{{ asset('build/js/push-subscribe.js') }}"></script>
@endauth
```

The banner stays hidden (`display:none`) by default and is only revealed by the inline script above when `Notification.permission === 'default'` (not yet granted or denied) — so a user who already answered the browser prompt, or a browser without Notification support, never sees it. `push-subscribe.js` (Step 5) attaches the click handlers to the same single element.

- [ ] **Step 6: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Job/PushSubscriptionTest.php`
Expected: PASS (1/1)

Run: `php artisan test` (full suite)
Expected: all pass, including every prior job-tracking test and the pre-existing 92.

- [ ] **Step 7: Commit**

```bash
git add composer.json composer.lock app/Models/User.php app/Notifications/JobDecisionNotification.php public/sw.js public/build/js/push-subscribe.js resources/views/layouts/topbar.blade.php .env.example app/Http/Controllers/Job/PushSubscriptionController.php routes/web.php tests/Feature/Job/PushSubscriptionTest.php
git commit -m "feat(jobs): Web Push notifications alongside the in-app bell"
```

---

### Task 12: Worker UI

**Files:**
- Create: `resources/views/jobs/index.blade.php`
- Create: `resources/views/jobs/create.blade.php`
- Create: `resources/views/jobs/show.blade.php`
- Modify: `resources/views/layouts/sidebar.blade.php`
- Test: `tests/Feature/Job/JobWorkerUiTest.php`

**Interfaces:**
- Consumes: routes from Tasks 4-9 (`jobs.index`, `jobs.create`, `jobs.store`, `jobs.show`, `jobs.start`, `jobs.hold`, `jobs.resume`, `jobs.complete`, `jobs.photos.store`).

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Job/JobWorkerUiTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobWorkerUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_sees_own_jobs_including_rejected_with_reason(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->assignRole('Worker');
        Job::factory()->create(['created_by' => $worker->id, 'assigned_to' => $worker->id, 'status' => 'rejected', 'title' => 'Rejected Job Alpha', 'rejection_reason' => 'Not authorized']);

        $response = $this->actingAs($worker)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertSee('Rejected Job Alpha');
        $response->assertSee('Not authorized');
    }

    public function test_worker_does_not_see_other_workers_jobs(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->assignRole('Worker');
        $otherWorker = User::factory()->create();
        Job::factory()->create(['created_by' => $otherWorker->id, 'assigned_to' => $otherWorker->id, 'title' => 'Someone Elses Job']);

        $response = $this->actingAs($worker)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertDontSee('Someone Elses Job');
    }

    public function test_job_detail_shows_start_button_for_assigned_status(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->assignRole('Worker');
        $job = Job::factory()->create(['status' => 'assigned', 'assigned_to' => $worker->id]);

        $response = $this->actingAs($worker)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee(route('jobs.start', $job->id), false);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Job/JobWorkerUiTest.php`
Expected: FAIL (view `jobs.index` not found)

- [ ] **Step 3: Implement the views**

```blade
{{-- resources/views/jobs/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.data-table-card title="{{ auth()->user()->can('jobs.view-all') ? 'All Jobs' : 'My Jobs' }}"
        create-route="{{ route('jobs.create') }}" create-label="Request / Assign Job">
        <div class="row g-2">
            @forelse($jobs as $job)
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="{{ route('jobs.show', $job->id) }}" class="text-decoration-none text-body">
                        <div class="card border">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title mb-1">{{ $job->title }}</h5>
                                    <x-ui.status-badge
                                        :status="ucfirst(str_replace('_', ' ', $job->status))"
                                        :variant="match($job->status) {
                                            'completed' => 'success',
                                            'rejected' => 'danger',
                                            'on_hold' => 'warning',
                                            default => 'info',
                                        }"
                                        :icon="match($job->status) {
                                            'completed' => 'ri-checkbox-circle-line',
                                            'rejected' => 'ri-close-circle-line',
                                            'on_hold' => 'ri-pause-circle-line',
                                            default => 'ri-time-line',
                                        }" />
                                </div>
                                <p class="text-muted mb-1">{{ $job->machine->name ?? $job->site_name }}</p>
                                @if($job->status === 'rejected')
                                    <p class="text-danger small mb-0">{{ $job->rejection_reason }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <x-ui.empty-state icon="ri-briefcase-line" message="No jobs yet." />
                </div>
            @endforelse
        </div>
    </x-ui.data-table-card>
</div>
@endsection
```

```blade
{{-- resources/views/jobs/create.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $canAssign ? 'Request or Assign a Job' : 'Request a Job' }}</h5>
            <form action="{{ route('jobs.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="job-title">Title</label>
                    <input id="job-title" type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-description">Description</label>
                    <textarea id="job-description" name="description" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-machine">Machine (in-house)</label>
                    <select id="job-machine" name="machine_id" class="form-select">
                        <option value="">-- Outside site instead --</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-site">Outside Site Name</label>
                    <input id="job-site" type="text" name="site_name" class="form-control" placeholder="Only if not an in-house machine">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-priority">Priority</label>
                    <select id="job-priority" name="priority" class="form-select">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="job-due-date">Due Date (optional)</label>
                    <input id="job-due-date" type="date" name="due_date" class="form-control">
                </div>
                @if($canAssign)
                    <div class="mb-3">
                        <label class="form-label" for="job-assign">Assign to Worker (skips approval)</label>
                        <select id="job-assign" name="assigned_to" class="form-select">
                            <option value="">-- Leave blank to submit as your own request --</option>
                            @foreach(\App\Models\User::role('Worker')->get() as $worker)
                                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <x-ui.button variant="success" type="submit" icon="ri-send-plane-line" ariaLabel="Submit job">Submit</x-ui.button>
            </form>
        </div>
    </div>
</div>
@endsection
```

```blade
{{-- resources/views/jobs/show.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h4>{{ $job->title }}</h4>
                <x-ui.status-badge
                    :status="ucfirst(str_replace('_', ' ', $job->status))"
                    variant="info"
                    icon="ri-briefcase-line" />
            </div>
            <p>{{ $job->description }}</p>
            <p class="text-muted">{{ $job->machine->name ?? $job->site_name }} &middot; Priority: {{ ucfirst($job->priority) }}</p>

            @if($job->status === 'rejected')
                <div class="alert alert-danger">Rejected: {{ $job->rejection_reason }}</div>
            @endif
            @if($job->status === 'on_hold')
                <div class="alert alert-warning">On hold: {{ $job->on_hold_reason }}</div>
            @endif

            @if($job->assigned_to === auth()->id())
                <div class="d-flex gap-2 my-3">
                    @if($job->status === 'assigned')
                        <form action="{{ route('jobs.start', $job->id) }}" method="POST">
                            @csrf
                            <x-ui.button variant="success" type="submit" icon="ri-play-circle-line" ariaLabel="Start job">Start</x-ui.button>
                        </form>
                    @endif
                    @if($job->status === 'in_progress')
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#holdJobModal">
                            <i class="ri-pause-circle-line"></i> Put On Hold
                        </button>
                    @endif
                    @if($job->status === 'on_hold')
                        <form action="{{ route('jobs.resume', $job->id) }}" method="POST">
                            @csrf
                            <x-ui.button variant="success" type="submit" icon="ri-play-circle-line" ariaLabel="Resume job">Resume</x-ui.button>
                        </form>
                    @endif
                </div>

                @if($job->status === 'in_progress')
                    <div class="card border">
                        <div class="card-body">
                            <h6>Add Proof Photo</h6>
                            <form id="photo-upload-form" action="{{ route('jobs.photos.store', $job->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="photo" accept="image/*" capture="environment" required class="form-control mb-2">
                                <input type="hidden" name="latitude" id="photo-lat">
                                <input type="hidden" name="longitude" id="photo-lng">
                                <p id="location-status" class="small text-muted">Checking location…</p>
                                <x-ui.button variant="primary" type="submit" icon="ri-upload-line" ariaLabel="Upload photo">Upload</x-ui.button>
                            </form>
                        </div>
                    </div>

                    @if($job->photos->isNotEmpty())
                        <form action="{{ route('jobs.complete', $job->id) }}" method="POST" class="mt-3">
                            @csrf
                            <label class="form-label" for="completion-notes">Completion Notes</label>
                            <textarea id="completion-notes" name="completion_notes" class="form-control mb-2" required></textarea>
                            <x-ui.button variant="success" type="submit" icon="ri-checkbox-circle-line" ariaLabel="Complete job">Complete Job</x-ui.button>
                        </form>
                    @endif
                @endif
            @endif

            <h6 class="mt-4">Photos</h6>
            <div class="row g-2">
                @forelse($job->photos as $photo)
                    <div class="col-6 col-md-3">
                        <img src="{{ asset('storage/' . $photo->path) }}" class="img-fluid rounded">
                        @if($photo->location_captured)
                            <a href="{{ $photo->map_link }}" target="_blank" class="small d-block">{{ $photo->address ?? 'View location' }}</a>
                        @else
                            <span class="small text-danger d-block">Location not captured</span>
                        @endif
                    </div>
                @empty
                    <div class="col-12"><x-ui.empty-state icon="ri-image-line" message="No photos uploaded yet." /></div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="modal fade" id="holdJobModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('jobs.hold', $job->id) }}" method="POST">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Put Job On Hold</h5></div>
                    <div class="modal-body">
                        <label class="form-label" for="on-hold-reason">Reason (required)</label>
                        <textarea id="on-hold-reason" name="on_hold_reason" class="form-control" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <x-ui.button variant="secondary" type="button" data-bs-dismiss="modal">Cancel</x-ui.button>
                        <x-ui.button variant="warning" type="submit">Confirm Hold</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function (position) {
        document.getElementById('photo-lat').value = position.coords.latitude;
        document.getElementById('photo-lng').value = position.coords.longitude;
        var status = document.getElementById('location-status');
        if (status) { status.textContent = 'Location captured ✓'; status.classList.replace('text-muted', 'text-success'); }
    }, function () {
        var status = document.getElementById('location-status');
        if (status) { status.textContent = 'Location not available ⚠'; status.classList.replace('text-muted', 'text-danger'); }
    });
}
</script>
@endsection
```

```blade
{{-- resources/views/layouts/sidebar.blade.php — add near other @can-gated items --}}
@can('jobs.view-own')
<li class="nav-item">
    <a class="nav-link menu-link" href="{{ route('jobs.index') }}">
        <i class="ri-briefcase-4-line"></i> <span>Jobs</span>
    </a>
</li>
@endcan
```

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Job/JobWorkerUiTest.php`
Expected: PASS (3/3)

- [ ] **Step 5: Commit**

```bash
git add resources/views/jobs/index.blade.php resources/views/jobs/create.blade.php resources/views/jobs/show.blade.php resources/views/layouts/sidebar.blade.php tests/Feature/Job/JobWorkerUiTest.php
git commit -m "feat(jobs): worker-facing job list, request form, and detail/completion UI"
```

---

### Task 13: Manager UI

**Files:**
- Create: `resources/views/jobs/pending-approval.blade.php`
- Modify: `resources/views/jobs/show.blade.php` (add manager panel: approve/reject, reassign, audit timeline)
- Modify: `resources/views/layouts/sidebar.blade.php`
- Test: `tests/Feature/Job/JobManagerUiTest.php`

**Interfaces:**
- Consumes: `jobs.pending-approval`, `jobs.approve`, `jobs.reject`, `jobs.reassign` routes (Tasks 5, 9).

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Job/JobManagerUiTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobManagerUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_approval_queue_lists_requests(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        Job::factory()->create(['status' => 'pending_approval', 'title' => 'Queue Item One']);

        $response = $this->actingAs($owner)->get(route('jobs.pending-approval'));

        $response->assertOk();
        $response->assertSee('Queue Item One');
    }

    public function test_manager_sees_approve_reject_actions_on_pending_job(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['status' => 'pending_approval']);

        $response = $this->actingAs($owner)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee(route('jobs.approve', $job->id), false);
        $response->assertSee(route('jobs.reject', $job->id), false);
    }

    public function test_manager_sees_audit_trail_on_job_detail(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $job = Job::factory()->create(['status' => 'assigned']);
        app(\App\Services\Job\JobAuditLogger::class)->log($job, $owner, 'assigned', 'Manager assigned this job directly.');

        $response = $this->actingAs($owner)->get(route('jobs.show', $job->id));

        $response->assertOk();
        $response->assertSee('Manager assigned this job directly.');
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Job/JobManagerUiTest.php`
Expected: FAIL (view `jobs.pending-approval` not found)

- [ ] **Step 3: Implement**

```blade
{{-- resources/views/jobs/pending-approval.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.data-table-card title="Pending Approval">
        @forelse($jobs as $job)
            <div class="card border mb-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('jobs.show', $job->id) }}">{{ $job->title }}</a>
                        <p class="text-muted mb-0 small">Requested by {{ $job->creator->name }} &middot; {{ $job->machine->name ?? $job->site_name }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('jobs.approve', $job->id) }}" method="POST">
                            @csrf
                            <x-ui.button variant="success" type="submit" icon="ri-checkbox-circle-line" ariaLabel="Approve job">Approve</x-ui.button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $job->id }}">
                            <i class="ri-close-circle-line"></i> Reject
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="rejectModal{{ $job->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('jobs.reject', $job->id) }}" method="POST">
                            @csrf
                            <div class="modal-header"><h5 class="modal-title">Reject "{{ $job->title }}"</h5></div>
                            <div class="modal-body">
                                <label class="form-label" for="rejection-reason-{{ $job->id }}">Reason (required)</label>
                                <textarea id="rejection-reason-{{ $job->id }}" name="rejection_reason" class="form-control" required></textarea>
                            </div>
                            <div class="modal-footer">
                                <x-ui.button variant="secondary" type="button" data-bs-dismiss="modal">Cancel</x-ui.button>
                                <x-ui.button variant="danger" type="submit">Confirm Reject</x-ui.button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <x-ui.empty-state icon="ri-checkbox-circle-line" message="No jobs waiting for approval." />
        @endforelse
    </x-ui.data-table-card>
</div>
@endsection
```

```blade
{{-- resources/views/jobs/show.blade.php — add this block just before the closing "Photos" section's parent </div>, gated to managers --}}
@can('jobs.approve')
    @if($job->status === 'pending_approval')
        <div class="d-flex gap-2 my-3">
            <form action="{{ route('jobs.approve', $job->id) }}" method="POST">
                @csrf
                <x-ui.button variant="success" type="submit" icon="ri-checkbox-circle-line" ariaLabel="Approve job">Approve</x-ui.button>
            </form>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectJobModal">
                <i class="ri-close-circle-line"></i> Reject
            </button>
        </div>
        <div class="modal fade" id="rejectJobModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('jobs.reject', $job->id) }}" method="POST">
                        @csrf
                        <div class="modal-header"><h5 class="modal-title">Reject Job</h5></div>
                        <div class="modal-body">
                            <label class="form-label" for="reject-reason">Reason (required)</label>
                            <textarea id="reject-reason" name="rejection_reason" class="form-control" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <x-ui.button variant="secondary" type="button" data-bs-dismiss="modal">Cancel</x-ui.button>
                            <x-ui.button variant="danger" type="submit">Confirm Reject</x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endcan

@can('jobs.assign')
    @if(in_array($job->status, ['assigned', 'in_progress', 'on_hold']))
        <form action="{{ route('jobs.reassign', $job->id) }}" method="POST" class="d-flex gap-2 align-items-end my-3">
            @csrf
            <div>
                <label class="form-label" for="reassign-to">Reassign to</label>
                <select id="reassign-to" name="assigned_to" class="form-select">
                    @foreach(\App\Models\User::role('Worker')->get() as $worker)
                        <option value="{{ $worker->id }}" @selected($worker->id === $job->assigned_to)>{{ $worker->name }}</option>
                    @endforeach
                </select>
            </div>
            <x-ui.button variant="secondary" type="submit" icon="ri-user-shared-line" ariaLabel="Reassign job">Reassign</x-ui.button>
        </form>
    @endif
@endcan

@can('jobs.view-all')
    <h6 class="mt-4">Audit Trail</h6>
    <ul class="list-group">
        @foreach($job->auditLogs()->orderBy('created_at')->get() as $log)
            <li class="list-group-item">
                <strong>{{ $log->user->name }}</strong> — {{ $log->description }}
                <span class="text-muted small float-end">{{ $log->created_at->format('d M Y, H:i') }}</span>
            </li>
        @endforeach
    </ul>
@endcan
```

```blade
{{-- resources/views/layouts/sidebar.blade.php — add near the Jobs link from Task 12 --}}
@can('jobs.approve')
<li class="nav-item">
    <a class="nav-link menu-link" href="{{ route('jobs.pending-approval') }}">
        <i class="ri-inbox-line"></i> <span>Pending Approval</span>
    </a>
</li>
@endcan
```

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Job/JobManagerUiTest.php`
Expected: PASS (3/3)

- [ ] **Step 5: Commit**

```bash
git add resources/views/jobs/pending-approval.blade.php resources/views/jobs/show.blade.php resources/views/layouts/sidebar.blade.php tests/Feature/Job/JobManagerUiTest.php
git commit -m "feat(jobs): manager pending-approval queue, reassignment, and audit trail view"
```

---

### Task 14: Owner Dashboard Widgets

**Files:**
- Modify: `app/Services/Home/DashboardService.php`
- Modify: `resources/views/index.blade.php`
- Test: `tests/Feature/Job/JobDashboardWidgetTest.php`

**Interfaces:**
- Consumes: `JobRepositoryInterface::ownerDashboardStats()` (Task 3).
- Modifies: `DashboardService::getDashboardViewData()` return array — **adds** a `jobStats` key, does not remove or rename any existing key (preserves the existing dashboard's behavior exactly, per `DEVELOPMENT-STANDARDS.md` §3).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Job/JobDashboardWidgetTest.php
<?php

namespace Tests\Feature\Job;

use App\Models\Job;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobDashboardWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_job_stats_without_breaking_existing_widgets(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        Job::factory()->create(['status' => 'pending_approval']);
        Job::factory()->create(['status' => 'completed', 'completed_at' => now()]);

        $response = $this->actingAs($owner)->get(route('root'));

        $response->assertOk();
        $response->assertViewHas('jobStats');
        // Existing dashboard keys must still be present — preserved behavior.
        $response->assertViewHas('totalRevenue');
        $response->assertViewHas('totalInvoices');
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Job/JobDashboardWidgetTest.php`
Expected: FAIL (`jobStats` view data missing)

- [ ] **Step 3: Implement**

```php
// app/Services/Home/DashboardService.php — full updated file
<?php

namespace App\Services\Home;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\Contracts\JobRepositoryInterface;

class DashboardService
{
    public function __construct(
        private DashboardRepositoryInterface $repository,
        private JobRepositoryInterface $jobRepository,
    ) {
    }

    public function getDashboardViewData(): array
    {
        return [
            'totalRevenue' => $this->repository->getTotalRevenue(),
            'totalInvoices' => $this->repository->getTotalInvoices(),
            'totalCustomers' => $this->repository->getTotalCustomers(),
            'pendingPayments' => $this->repository->getPendingPayments(),
            'topProducts' => $this->repository->getTopProducts(),
            'recentInvoices' => $this->repository->getRecentInvoices(),
            'jobStats' => $this->jobRepository->ownerDashboardStats(),
        ];
    }
}
```

```blade
{{-- resources/views/index.blade.php — add this widget row; find the existing top-level
     <div class="row"> that holds the existing revenue/invoice/customer widget cards and
     insert this as an additional row immediately after it, so nothing existing is removed
     or reordered --}}
@can('jobs.view-all')
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-0">Jobs Completed Today</p>
                <h4 class="mb-0">{{ $jobStats['completed_today'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-0">Pending Approval</p>
                <h4 class="mb-0">{{ $jobStats['pending_approval'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-0">Pending Completion</p>
                <h4 class="mb-0">{{ $jobStats['pending_completion'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <p class="text-uppercase fw-medium text-muted mb-0">Jobs by Worker (Today)</p>
                @forelse($jobStats['by_worker'] as $workerName => $count)
                    <p class="mb-0 small">{{ $workerName }}: {{ $count }}</p>
                @empty
                    <p class="mb-0 small text-muted">No completions yet today.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endcan
```

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Job/JobDashboardWidgetTest.php`
Expected: PASS (1/1)

Run: `php artisan test` (full suite — final check for this plan)
Expected: all pass, no regressions to the pre-existing 92 tests.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Home/DashboardService.php resources/views/index.blade.php tests/Feature/Job/JobDashboardWidgetTest.php
git commit -m "feat(jobs): owner dashboard widgets for job completion/approval stats"
```
