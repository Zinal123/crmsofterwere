# Workforce, Attendance & Payroll Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a self-contained Employee Master, daily Attendance, and full Payroll calculation module — no existing table, route, controller, or view is modified except the two explicitly-listed additive touches.

**Architecture:** Follows this codebase's established Controller → Service → Repository pattern exactly (see `docs/DEVELOPMENT-STANDARDS.md`), a new `Workforce` domain alongside the existing Product/Inventory/Quotation/Invoice/Admin/Job domains, reusing `App\Support\ImageCompressor` for image ID-document uploads and the design-system component library (`<x-ui.button>`, `<x-ui.status-badge>`, `<x-ui.empty-state>`, `<x-ui.data-table-card>`).

**Tech Stack:** Laravel 10, Blade, spatie/laravel-permission (existing). No new composer dependency.

Reference spec: `docs/superpowers/specs/2026-07-19-workforce-attendance-payroll-design.md` — read it before starting; this plan implements it task by task.

## Global Constraints

- **No existing table, route, controller, or view is modified**, except two additive touches: `resources/views/layouts/sidebar.blade.php` (new nav links, Task 10) and `database/seeders/RolesAndPermissionsSeeder.php` (6 new permissions added, existing ones untouched, Task 2).
- Every repository **read** goes through `App\Support\Tenancy\TenantScope::apply()` (already exists, no-op today) — same convention as every other domain. Writes don't go through it.
- Attendance status enum values (exact strings): `present`, `absent`, `half_day`, `leave`.
- Pay type enum values (exact strings): `monthly`, `daily`.
- Document type enum values (exact strings): `aadhar`, `pan`, `driving_license`, `voter_id`, `other`.
- Permission strings (exact, added to `Database\Seeders\RolesAndPermissionsSeeder::PERMISSIONS`): `employees.view`, `employees.manage`, `attendance.view`, `attendance.manage`, `payroll.view`, `payroll.manage-payments`.
- `Owner` role gets all six (matches existing seeder pattern). `Worker` role gets none of them — this module is Owner/Manager-only, no exceptions.
- **This app has a documented `{any}` catch-all route** (`routes/web.php`) that silently intercepts any single-URL-segment GET route registered after it. `GET employees` and `GET attendance` are both single-segment — they MUST be registered in the existing pre-catch-all `Route::middleware('auth')->group(...)` block (the one already containing `jobs`, `machines`, `product`, etc.), not the later auth group. This exact bug has been independently rediscovered and fixed 4 times across two earlier features in this codebase — do not rediscover it a fifth time, just register correctly from the start. Update the block's explanatory comment count (currently "9 routes") when adding to it.
- **Payroll math is exact, not approximate — this task's tests must assert precise decimal values, not just "some" earnings.** Day rate for `pay_type = daily` is `pay_rate` directly; for `pay_type = monthly` it's `pay_rate / <days in that calendar month>` (use PHP's `cal_days_in_month()` or `Carbon::createFromDate($year, $month, 1)->daysInMonth`). Present = 1.0× day rate, half_day = 0.5×, leave/absent = 0.
- `database/factories/UserFactory.php` auto-assigns the **Owner** role to every factory-created user via an `afterCreating` hook. Any test needing an isolated single-role user MUST use `$user->syncRoles([...])`, never `$user->assignRole(...)` — this exact test-isolation bug was found and fixed once in an earlier feature in this codebase and must not recur here.
- Every task's tests live in `tests/Feature/Workforce/*Test.php`, use `RefreshDatabase`, and use `database/factories/*Factory.php` for the models under test.

---

### Task 1: Migrations, Models, Factories

**Files:**
- Create: `database/migrations/2026_07_19_000001_create_employees_table.php`
- Create: `database/migrations/2026_07_19_000002_create_attendances_table.php`
- Create: `database/migrations/2026_07_19_000003_create_salary_payments_table.php`
- Create: `database/migrations/2026_07_19_000004_create_employee_documents_table.php`
- Create: `app/Models/Employee.php`
- Create: `app/Models/Attendance.php`
- Create: `app/Models/SalaryPayment.php`
- Create: `app/Models/EmployeeDocument.php`
- Create: `database/factories/EmployeeFactory.php`
- Create: `database/factories/AttendanceFactory.php`
- Test: `tests/Feature/Workforce/WorkforceModelsMigrationTest.php`

**Interfaces:**
- Produces: `Employee` (fillable: `name`, `phone`, `email`, `address`, `emergency_contact_name`, `emergency_contact_phone`, `department`, `designation`, `joining_date`, `pay_type`, `pay_rate`, `overtime_rate_per_hour`, `bank_account_holder_name`, `bank_account_number`, `bank_ifsc`, `bank_name`, `user_id`, `is_active`), `Attendance` (fillable: `employee_id`, `date`, `status`, `overtime_hours`, `marked_by`), `SalaryPayment` (fillable: `employee_id`, `date`, `amount`, `note`, `paid_by`), `EmployeeDocument` (fillable: `employee_id`, `document_type`, `document_number`, `path`, `uploaded_by`).
- Relationships: `Employee::attendances()` hasMany `Attendance`; `Employee::salaryPayments()` hasMany `SalaryPayment`; `Employee::documents()` hasMany `EmployeeDocument`; `Employee::user()` belongsTo `User` (nullable); `Attendance::employee()` belongsTo `Employee`; `Attendance::marker()` belongsTo `User` via `marked_by`; `SalaryPayment::employee()` belongsTo `Employee`; `SalaryPayment::payer()` belongsTo `User` via `paid_by`; `EmployeeDocument::employee()` belongsTo `Employee`.

**IMPORTANT — column type note:** this app's live `users.id` column is a legacy signed `int`, NOT Laravel's default unsigned bigint (discovered and fixed in an earlier feature — see `2026_07_18_000007_create_jobs_table.php` for the reference fix). Any foreign key referencing `users.id` (`marked_by`, `paid_by`, `uploaded_by`, `user_id` on employees) MUST use `$table->integer('column_name')` + an explicit `$table->foreign('column_name')->references('id')->on('users')`, NOT `$table->foreignId(...)->constrained('users')` (which creates an incompatible unsigned bigint column and will fail with a MySQL FK type error against the live database, even though it works fine against sqlite in automated tests due to sqlite's weak typing). Foreign keys referencing `employees.id` (a table this task creates fresh) should use normal `$table->foreignId('employee_id')->constrained('employees')` — that one is fine, no type mismatch.

- [ ] **Step 1: Write the migrations**

```php
// database/migrations/2026_07_19_000001_create_employees_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->date('joining_date');
            $table->enum('pay_type', ['monthly', 'daily']);
            $table->decimal('pay_rate', 10, 2);
            $table->decimal('overtime_rate_per_hour', 10, 2)->default(0);
            $table->string('bank_account_holder_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc')->nullable();
            $table->string('bank_name')->nullable();
            $table->integer('user_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
```

```php
// database/migrations/2026_07_19_000002_create_attendances_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'half_day', 'leave']);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->integer('marked_by');
            $table->timestamps();

            $table->foreign('marked_by')->references('id')->on('users');
            $table->unique(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
```

```php
// database/migrations/2026_07_19_000003_create_salary_payments_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->string('note')->nullable();
            $table->integer('paid_by');
            $table->timestamps();

            $table->foreign('paid_by')->references('id')->on('users');
            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
```

```php
// database/migrations/2026_07_19_000004_create_employee_documents_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('document_type', ['aadhar', 'pan', 'driving_license', 'voter_id', 'other']);
            $table->string('document_number')->nullable();
            $table->string('path');
            $table->integer('uploaded_by');
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
```

- [ ] **Step 2: Write the models**

```php
// app/Models/Employee.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'address', 'emergency_contact_name', 'emergency_contact_phone',
        'department', 'designation', 'joining_date', 'pay_type', 'pay_rate', 'overtime_rate_per_hour',
        'bank_account_holder_name', 'bank_account_number', 'bank_ifsc', 'bank_name', 'user_id', 'is_active',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'pay_rate' => 'decimal:2',
        'overtime_rate_per_hour' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

```php
// app/Models/Attendance.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['employee_id', 'date', 'status', 'overtime_hours', 'marked_by'];

    protected $casts = [
        'date' => 'date',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function marker()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
```

```php
// app/Models/SalaryPayment.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    protected $fillable = ['employee_id', 'date', 'amount', 'note', 'paid_by'];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
```

```php
// app/Models/EmployeeDocument.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = ['employee_id', 'document_type', 'document_number', 'path', 'uploaded_by'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
```

- [ ] **Step 3: Write the factories**

```php
// database/factories/EmployeeFactory.php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->numerify('##########'),
            'joining_date' => now()->subMonths(3)->toDateString(),
            'pay_type' => 'daily',
            'pay_rate' => 600,
            'overtime_rate_per_hour' => 50,
            'is_active' => true,
        ];
    }
}
```

```php
// database/factories/AttendanceFactory.php
<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        return [
            'employee_id' => Employee::factory(),
            'date' => now()->toDateString(),
            'status' => 'present',
            'overtime_hours' => 0,
        ];
    }
}
```

- [ ] **Step 4: Write a migration/model smoke test**

```php
// tests/Feature/Workforce/WorkforceModelsMigrationTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\SalaryPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceModelsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_workforce_tables_migrate_and_relate_correctly(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'status' => 'present',
            'overtime_hours' => 2,
            'marked_by' => $user->id,
        ]);

        $payment = SalaryPayment::create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'amount' => 1000,
            'paid_by' => $user->id,
        ]);

        $document = EmployeeDocument::create([
            'employee_id' => $employee->id,
            'document_type' => 'aadhar',
            'path' => 'employee-documents/test.jpg',
            'uploaded_by' => $user->id,
        ]);

        $this->assertTrue($employee->attendances->first()->is($attendance));
        $this->assertTrue($employee->salaryPayments->first()->is($payment));
        $this->assertTrue($employee->documents->first()->is($document));
    }

    public function test_attendance_is_unique_per_employee_per_day(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-19', 'status' => 'present', 'marked_by' => $user->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-19', 'status' => 'absent', 'marked_by' => $user->id]);
    }
}
```

- [ ] **Step 5: Run the tests**

Run: `php artisan test tests/Feature/Workforce/WorkforceModelsMigrationTest.php`
Expected: PASS (2/2)

- [ ] **Step 6: Run `php artisan migrate` against the real local database, not just sqlite**

Run: `php artisan migrate`
Expected: all 4 new migrations run cleanly against the live MySQL database. This is a mandatory step, not optional — an earlier feature in this codebase shipped all 14 of its tasks with a green sqlite-only test suite while its migrations had silently never been run against the real database at all, and separately hit a real MySQL-only FK-type error this task's migrations are specifically written to avoid. Confirm `php artisan migrate:status` shows all 4 as `Ran` before proceeding.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_07_19_* app/Models/Employee.php app/Models/Attendance.php app/Models/SalaryPayment.php app/Models/EmployeeDocument.php database/factories/EmployeeFactory.php database/factories/AttendanceFactory.php tests/Feature/Workforce/WorkforceModelsMigrationTest.php
git commit -m "feat(workforce): add migrations, models, and factories for employee/attendance/payroll domain"
```

---

### Task 2: Permissions + Employee Repository/Service/Controller (CRUD)

**Files:**
- Modify: `database/seeders/RolesAndPermissionsSeeder.php`
- Create: `app/Repositories/Contracts/EmployeeRepositoryInterface.php`
- Create: `app/Repositories/Eloquent/EloquentEmployeeRepository.php`
- Create: `app/Services/Workforce/EmployeeService.php`
- Create: `app/Http/Controllers/Workforce/EmployeeController.php`
- Modify: `app/Providers/RepositoryServiceProvider.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Workforce/EmployeeTest.php`

**Interfaces:**
- Produces: `EmployeeRepositoryInterface::all(): Collection` (active + inactive, for the list view), `::activeOnly(): Collection` (used by attendance bulk-entry), `::find($id): ?Employee`, `::create(array $data): Employee`, `::update(Employee $employee, array $data): Employee`, `::deactivate($id): Employee`.

- [ ] **Step 1: Add the six workforce permissions to the seeder**

```php
// database/seeders/RolesAndPermissionsSeeder.php — add to the PERMISSIONS array, after the jobs.* entries:
        'employees.view', 'employees.manage', 'attendance.view', 'attendance.manage', 'payroll.view', 'payroll.manage-payments',
```

Owner already gets every permission via `syncPermissions(self::PERMISSIONS)` — no other seeder change needed. Worker gets none of these (its `syncPermissions` call already only lists `jobs.view-own`/`jobs.create` — do not add anything to it).

- [ ] **Step 2: Write the failing test**

```php
// tests/Feature/Workforce/EmployeeTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_role_has_all_six_workforce_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = \Spatie\Permission\Models\Role::findByName('Owner');

        $this->assertTrue($owner->hasPermissionTo('employees.view'));
        $this->assertTrue($owner->hasPermissionTo('employees.manage'));
        $this->assertTrue($owner->hasPermissionTo('attendance.view'));
        $this->assertTrue($owner->hasPermissionTo('attendance.manage'));
        $this->assertTrue($owner->hasPermissionTo('payroll.view'));
        $this->assertTrue($owner->hasPermissionTo('payroll.manage-payments'));
    }

    public function test_worker_role_has_none_of_the_workforce_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = \Spatie\Permission\Models\Role::findByName('Worker');

        $this->assertFalse($worker->hasPermissionTo('employees.view'));
        $this->assertFalse($worker->hasPermissionTo('attendance.manage'));
    }

    public function test_owner_can_create_an_employee(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->post(route('employees.store'), [
            'name' => 'Ramesh Kumar',
            'phone' => '9998887770',
            'joining_date' => '2026-01-15',
            'pay_type' => 'daily',
            'pay_rate' => 600,
        ]);

        $response->assertRedirect(route('employees.index'));
        $this->assertDatabaseHas('employees', ['name' => 'Ramesh Kumar', 'pay_type' => 'daily', 'is_active' => true]);
    }

    public function test_owner_can_deactivate_an_employee_without_deleting_history(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['is_active' => true]);

        $this->actingAs($owner)->post(route('employees.deactivate', $employee->id));

        $this->assertFalse($employee->fresh()->is_active);
        $this->assertDatabaseHas('employees', ['id' => $employee->id]);
    }

    public function test_worker_cannot_view_employees(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('employees.index'));

        $response->assertForbidden();
    }
}
```

- [ ] **Step 3: Run, confirm failure**

Run: `php artisan test tests/Feature/Workforce/EmployeeTest.php`
Expected: FAIL (route `employees.index` not defined)

- [ ] **Step 4: Repository interface + implementation**

```php
// app/Repositories/Contracts/EmployeeRepositoryInterface.php
<?php

namespace App\Repositories\Contracts;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;

interface EmployeeRepositoryInterface
{
    public function all(): Collection;

    public function activeOnly(): Collection;

    public function find($id): ?Employee;

    public function create(array $data): Employee;

    public function update(Employee $employee, array $data): Employee;

    public function deactivate($id): Employee;
}
```

```php
// app/Repositories/Eloquent/EloquentEmployeeRepository.php
<?php

namespace App\Repositories\Eloquent;

use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentEmployeeRepository implements EmployeeRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function all(): Collection
    {
        return $this->tenantScope->apply(Employee::orderBy('name'))->get();
    }

    public function activeOnly(): Collection
    {
        return $this->tenantScope->apply(Employee::where('is_active', true)->orderBy('name'))->get();
    }

    public function find($id): ?Employee
    {
        return $this->tenantScope->apply(Employee::query())->find($id);
    }

    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);

        return $employee;
    }

    public function deactivate($id): Employee
    {
        $employee = Employee::findOrFail($id);
        $employee->is_active = false;
        $employee->save();

        return $employee;
    }
}
```

- [ ] **Step 5: Service, Controller, route**

```php
// app/Services/Workforce/EmployeeService.php
<?php

namespace App\Services\Workforce;

use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EmployeeService
{
    public function __construct(private EmployeeRepositoryInterface $repository)
    {
    }

    public function list(): Collection
    {
        return $this->repository->all();
    }

    public function activeList(): Collection
    {
        return $this->repository->activeOnly();
    }

    public function find($id): ?Employee
    {
        return $this->repository->find($id);
    }

    public function create(array $data): Employee
    {
        $data['is_active'] = true;

        return $this->repository->create($data);
    }

    public function update($id, array $data): Employee
    {
        $employee = $this->repository->find($id);

        return $this->repository->update($employee, $data);
    }

    public function deactivate($id): Employee
    {
        return $this->repository->deactivate($id);
    }
}
```

```php
// app/Http/Controllers/Workforce/EmployeeController.php
<?php

namespace App\Http\Controllers\Workforce;

use App\Http\Controllers\Controller;
use App\Services\Workforce\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $service)
    {
    }

    public function index()
    {
        return view('employees.index', ['employees' => $this->service->list()]);
    }

    public function create()
    {
        return view('employees.create');
    }

    private function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'joining_date' => 'required|date',
            'pay_type' => 'required|in:monthly,daily',
            'pay_rate' => 'required|numeric|min:0',
            'overtime_rate_per_hour' => 'nullable|numeric|min:0',
            'bank_account_holder_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:255',
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());

        $this->service->create($data);

        return redirect()->route('employees.index')->with('success', 'Employee added.');
    }

    public function edit($id)
    {
        $employee = $this->service->find($id);
        abort_if(! $employee, 404);

        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate($this->validationRules());

        $this->service->update($id, $data);

        return redirect()->route('employees.index')->with('success', 'Employee updated.');
    }

    public function deactivate($id)
    {
        $this->service->deactivate($id);

        return redirect()->route('employees.index')->with('success', 'Employee deactivated.');
    }
}
```

```php
// app/Providers/RepositoryServiceProvider.php — add import + binding
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Eloquent\EloquentEmployeeRepository;
// ... inside register():
$this->app->bind(EmployeeRepositoryInterface::class, EloquentEmployeeRepository::class);
```

```php
// routes/web.php — add "employees" to the pre-catch-all group's comment count (9 -> 10)
// and add the GET route inside that same pre-catch-all Route::middleware('auth')->group block:
    Route::get('employees', [App\Http\Controllers\Workforce\EmployeeController::class, 'index'])->name('employees.index')->middleware('permission:employees.view');

// add these to the LATER auth group (multi-segment / POST, unaffected by the catch-all):
    Route::get('employees/create', [App\Http\Controllers\Workforce\EmployeeController::class, 'create'])->name('employees.create')->middleware('permission:employees.manage');
    Route::post('employees', [App\Http\Controllers\Workforce\EmployeeController::class, 'store'])->name('employees.store')->middleware('permission:employees.manage');
    Route::get('employees/{id}/edit', [App\Http\Controllers\Workforce\EmployeeController::class, 'edit'])->name('employees.edit')->middleware('permission:employees.manage');
    Route::put('employees/{id}', [App\Http\Controllers\Workforce\EmployeeController::class, 'update'])->name('employees.update')->middleware('permission:employees.manage');
    Route::post('employees/{id}/deactivate', [App\Http\Controllers\Workforce\EmployeeController::class, 'deactivate'])->name('employees.deactivate')->middleware('permission:employees.manage');
```

For `employees.create`/`employees.store`, a minimal placeholder view (`resources/views/employees/create.blade.php` with just `@extends('layouts.master')` and a `@section('content')` containing nothing but a heading) is acceptable for THIS task only — Task 8 replaces it with the real form. Do not skip creating the file; `create()`'s `view('employees.create')` must resolve or the route 500s.

- [ ] **Step 6: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Workforce/EmployeeTest.php`
Expected: PASS (5/5)

- [ ] **Step 7: Commit**

```bash
git add database/seeders/RolesAndPermissionsSeeder.php app/Repositories/Contracts/EmployeeRepositoryInterface.php app/Repositories/Eloquent/EloquentEmployeeRepository.php app/Services/Workforce/EmployeeService.php app/Http/Controllers/Workforce/EmployeeController.php app/Providers/RepositoryServiceProvider.php routes/web.php resources/views/employees/create.blade.php tests/Feature/Workforce/EmployeeTest.php
git commit -m "feat(workforce): add workforce permissions and employee CRUD"
```

---

### Task 3: Employee ID Document Upload

**Files:**
- Create: `app/Repositories/Contracts/EmployeeDocumentRepositoryInterface.php`
- Create: `app/Repositories/Eloquent/EloquentEmployeeDocumentRepository.php`
- Create: `app/Services/Workforce/EmployeeDocumentService.php`
- Modify: `app/Http/Controllers/Workforce/EmployeeController.php`
- Modify: `app/Providers/RepositoryServiceProvider.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Workforce/EmployeeDocumentTest.php`

**Interfaces:**
- Consumes: `App\Support\ImageCompressor` (existing, from the Job Tracking module — `compress(UploadedFile $file, string $destinationDiskPath, int $maxDimension = 1600, int $quality = 75): void`).
- Produces: `EmployeeDocumentService::upload(Employee $employee, User $uploader, UploadedFile $file, string $documentType, ?string $documentNumber): EmployeeDocument`. Images are compressed via `ImageCompressor`; PDFs (`application/pdf` mime type) are stored as-is via `Storage::disk('public')->putFileAs(...)` since `ImageCompressor` only handles image formats.

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Workforce/EmployeeDocumentTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_uploads_an_image_id_document(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $file = UploadedFile::fake()->image('aadhar.jpg', 1200, 800);

        $response = $this->actingAs($owner)->post(route('employees.documents.store', $employee->id), [
            'document_type' => 'aadhar',
            'document_number' => '1234-5678-9012',
            'document' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_documents', [
            'employee_id' => $employee->id,
            'document_type' => 'aadhar',
            'document_number' => '1234-5678-9012',
        ]);
    }

    public function test_owner_uploads_a_pdf_id_document(): void
    {
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        $file = UploadedFile::fake()->create('pan.pdf', 200, 'application/pdf');

        $response = $this->actingAs($owner)->post(route('employees.documents.store', $employee->id), [
            'document_type' => 'pan',
            'document' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_documents', ['employee_id' => $employee->id, 'document_type' => 'pan']);
        $document = $employee->documents()->first();
        Storage::disk('public')->assertExists($document->path);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Workforce/EmployeeDocumentTest.php`
Expected: FAIL (route `employees.documents.store` not defined)

- [ ] **Step 3: Implement**

```php
// app/Repositories/Contracts/EmployeeDocumentRepositoryInterface.php
<?php

namespace App\Repositories\Contracts;

use App\Models\EmployeeDocument;

interface EmployeeDocumentRepositoryInterface
{
    public function create(array $data): EmployeeDocument;
}
```

```php
// app/Repositories/Eloquent/EloquentEmployeeDocumentRepository.php
<?php

namespace App\Repositories\Eloquent;

use App\Models\EmployeeDocument;
use App\Repositories\Contracts\EmployeeDocumentRepositoryInterface;

class EloquentEmployeeDocumentRepository implements EmployeeDocumentRepositoryInterface
{
    public function create(array $data): EmployeeDocument
    {
        return EmployeeDocument::create($data);
    }
}
```

```php
// app/Services/Workforce/EmployeeDocumentService.php
<?php

namespace App\Services\Workforce;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Repositories\Contracts\EmployeeDocumentRepositoryInterface;
use App\Support\ImageCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentService
{
    public function __construct(
        private EmployeeDocumentRepositoryInterface $repository,
        private ImageCompressor $compressor,
    ) {
    }

    public function upload(Employee $employee, User $uploader, UploadedFile $file, string $documentType, ?string $documentNumber): EmployeeDocument
    {
        $isImage = str_starts_with($file->getMimeType(), 'image/');
        $extension = $isImage ? 'jpg' : $file->getClientOriginalExtension();
        $diskPath = 'employee-documents/' . $employee->id . '/' . uniqid('doc_', true) . '.' . $extension;

        if ($isImage) {
            $this->compressor->compress($file, $diskPath);
        } else {
            Storage::disk('public')->putFileAs(dirname($diskPath), $file, basename($diskPath));
        }

        return $this->repository->create([
            'employee_id' => $employee->id,
            'document_type' => $documentType,
            'document_number' => $documentNumber,
            'path' => $diskPath,
            'uploaded_by' => $uploader->id,
        ]);
    }
}
```

```php
// app/Http/Controllers/Workforce/EmployeeController.php — add constructor param + method
// constructor becomes:
    public function __construct(
        private EmployeeService $service,
        private \App\Services\Workforce\EmployeeDocumentService $documentService,
    ) {
    }

// new method inside the class:
    public function storeDocument(Request $request, $id)
    {
        $data = $request->validate([
            'document_type' => 'required|in:aadhar,pan,driving_license,voter_id,other',
            'document_number' => 'nullable|string|max:100',
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);
        $employee = $this->service->find($id);
        abort_if(! $employee, 404);

        $this->documentService->upload($employee, $request->user(), $request->file('document'), $data['document_type'], $data['document_number'] ?? null);

        return redirect()->route('employees.edit', $employee->id)->with('success', 'Document uploaded.');
    }
```

```php
// app/Providers/RepositoryServiceProvider.php — add import + binding
use App\Repositories\Contracts\EmployeeDocumentRepositoryInterface;
use App\Repositories\Eloquent\EloquentEmployeeDocumentRepository;
// ... inside register():
$this->app->bind(EmployeeDocumentRepositoryInterface::class, EloquentEmployeeDocumentRepository::class);
```

```php
// routes/web.php — add to the later auth group:
    Route::post('employees/{id}/documents', [App\Http\Controllers\Workforce\EmployeeController::class, 'storeDocument'])->name('employees.documents.store')->middleware('permission:employees.manage');
```

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Workforce/EmployeeDocumentTest.php`
Expected: PASS (2/2)

- [ ] **Step 5: Commit**

```bash
git add app/Repositories/Contracts/EmployeeDocumentRepositoryInterface.php app/Repositories/Eloquent/EloquentEmployeeDocumentRepository.php app/Services/Workforce/EmployeeDocumentService.php app/Http/Controllers/Workforce/EmployeeController.php app/Providers/RepositoryServiceProvider.php routes/web.php tests/Feature/Workforce/EmployeeDocumentTest.php
git commit -m "feat(workforce): employee ID document upload (image compression + PDF passthrough)"
```

---

### Task 4: Attendance Bulk Marking

**Files:**
- Create: `app/Repositories/Contracts/AttendanceRepositoryInterface.php`
- Create: `app/Repositories/Eloquent/EloquentAttendanceRepository.php`
- Create: `app/Services/Workforce/AttendanceService.php`
- Create: `app/Http/Controllers/Workforce/AttendanceController.php`
- Modify: `app/Providers/RepositoryServiceProvider.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Workforce/AttendanceTest.php`

**Interfaces:**
- Produces: `AttendanceRepositoryInterface::upsertForDate(string $date, array $rows): void` where `$rows` is `[['employee_id' => int, 'status' => string, 'overtime_hours' => float, 'marked_by' => int], ...]` — uses `updateOrCreate` per row keyed on `(employee_id, date)`, matching the unique constraint from Task 1. `::forDate(string $date): Collection` (attendance rows for a given date, with `employee` eager-loaded). `::forEmployeeAndMonth(int $employeeId, int $year, int $month): Collection` (used by Task 5's register view AND Task 6's payroll calculation).

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Workforce/AttendanceTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_bulk_marks_attendance_for_a_date(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $emp1 = Employee::factory()->create();
        $emp2 = Employee::factory()->create();

        $response = $this->actingAs($owner)->post(route('attendance.store'), [
            'date' => '2026-07-19',
            'rows' => [
                ['employee_id' => $emp1->id, 'status' => 'present', 'overtime_hours' => 1.5],
                ['employee_id' => $emp2->id, 'status' => 'half_day', 'overtime_hours' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', ['employee_id' => $emp1->id, 'date' => '2026-07-19', 'status' => 'present', 'overtime_hours' => 1.5]);
        $this->assertDatabaseHas('attendances', ['employee_id' => $emp2->id, 'date' => '2026-07-19', 'status' => 'half_day']);
    }

    public function test_remarking_the_same_date_updates_not_duplicates(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();

        $this->actingAs($owner)->post(route('attendance.store'), [
            'date' => '2026-07-19',
            'rows' => [['employee_id' => $employee->id, 'status' => 'absent', 'overtime_hours' => 0]],
        ]);
        $this->actingAs($owner)->post(route('attendance.store'), [
            'date' => '2026-07-19',
            'rows' => [['employee_id' => $employee->id, 'status' => 'present', 'overtime_hours' => 2]],
        ]);

        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', ['employee_id' => $employee->id, 'status' => 'present', 'overtime_hours' => 2]);
    }

    public function test_worker_cannot_mark_attendance(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $employee = Employee::factory()->create();

        $response = $this->actingAs($worker)->post(route('attendance.store'), [
            'date' => '2026-07-19',
            'rows' => [['employee_id' => $employee->id, 'status' => 'present', 'overtime_hours' => 0]],
        ]);

        $response->assertForbidden();
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Workforce/AttendanceTest.php`
Expected: FAIL (route `attendance.store` not defined)

- [ ] **Step 3: Implement**

```php
// app/Repositories/Contracts/AttendanceRepositoryInterface.php
<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface
{
    public function upsertForDate(string $date, array $rows): void;

    public function forDate(string $date): Collection;

    public function forEmployeeAndMonth(int $employeeId, int $year, int $month): Collection;
}
```

```php
// app/Repositories/Eloquent/EloquentAttendanceRepository.php
<?php

namespace App\Repositories\Eloquent;

use App\Models\Attendance;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentAttendanceRepository implements AttendanceRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function upsertForDate(string $date, array $rows): void
    {
        foreach ($rows as $row) {
            Attendance::updateOrCreate(
                ['employee_id' => $row['employee_id'], 'date' => $date],
                [
                    'status' => $row['status'],
                    'overtime_hours' => $row['overtime_hours'] ?? 0,
                    'marked_by' => $row['marked_by'],
                ]
            );
        }
    }

    public function forDate(string $date): Collection
    {
        return $this->tenantScope->apply(Attendance::where('date', $date))->with('employee')->get();
    }

    public function forEmployeeAndMonth(int $employeeId, int $year, int $month): Collection
    {
        return $this->tenantScope->apply(
            Attendance::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
        )->orderBy('date')->get();
    }
}
```

```php
// app/Services/Workforce/AttendanceService.php
<?php

namespace App\Services\Workforce;

use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AttendanceService
{
    public function __construct(private AttendanceRepositoryInterface $repository)
    {
    }

    public function markForDate(string $date, array $rows, User $marker): void
    {
        $rows = array_map(function ($row) use ($marker) {
            $row['marked_by'] = $marker->id;

            return $row;
        }, $rows);

        $this->repository->upsertForDate($date, $rows);
    }

    public function forDate(string $date): Collection
    {
        return $this->repository->forDate($date);
    }

    public function forEmployeeAndMonth(int $employeeId, int $year, int $month): Collection
    {
        return $this->repository->forEmployeeAndMonth($employeeId, $year, $month);
    }
}
```

```php
// app/Http/Controllers/Workforce/AttendanceController.php
<?php

namespace App\Http\Controllers\Workforce;

use App\Http\Controllers\Controller;
use App\Services\Workforce\AttendanceService;
use App\Services\Workforce\EmployeeService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $service,
        private EmployeeService $employeeService,
    ) {
    }

    public function mark(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $employees = $this->employeeService->activeList();
        $existing = $this->service->forDate($date)->keyBy('employee_id');

        return view('attendance.mark', compact('date', 'employees', 'existing'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'rows' => 'required|array|min:1',
            'rows.*.employee_id' => 'required|exists:employees,id',
            'rows.*.status' => 'required|in:present,absent,half_day,leave',
            'rows.*.overtime_hours' => 'nullable|numeric|min:0',
        ]);

        $this->service->markForDate($data['date'], $data['rows'], $request->user());

        return redirect()->route('attendance.mark', ['date' => $data['date']])->with('success', 'Attendance saved.');
    }
}
```

```php
// app/Providers/RepositoryServiceProvider.php — add import + binding
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Eloquent\EloquentAttendanceRepository;
// ... inside register():
$this->app->bind(AttendanceRepositoryInterface::class, EloquentAttendanceRepository::class);
```

```php
// routes/web.php — "attendance" is a single-segment GET, must go in the pre-catch-all
// block (update the comment count from 10 to 11):
    Route::get('attendance', [App\Http\Controllers\Workforce\AttendanceController::class, 'mark'])->name('attendance.mark')->middleware('permission:attendance.manage');

// this one is POST, safe in the later auth group:
    Route::post('attendance', [App\Http\Controllers\Workforce\AttendanceController::class, 'store'])->name('attendance.store')->middleware('permission:attendance.manage');
```

Create a minimal placeholder `resources/views/attendance/mark.blade.php` for this task (just `@extends('layouts.master')` + a heading) — Task 9 replaces it with the real bulk-entry UI.

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Workforce/AttendanceTest.php`
Expected: PASS (3/3)

- [ ] **Step 5: Commit**

```bash
git add app/Repositories/Contracts/AttendanceRepositoryInterface.php app/Repositories/Eloquent/EloquentAttendanceRepository.php app/Services/Workforce/AttendanceService.php app/Http/Controllers/Workforce/AttendanceController.php app/Providers/RepositoryServiceProvider.php routes/web.php resources/views/attendance/mark.blade.php tests/Feature/Workforce/AttendanceTest.php
git commit -m "feat(workforce): bulk daily attendance marking with per-employee upsert"
```

---

### Task 5: Monthly Attendance Register

**Files:**
- Modify: `app/Http/Controllers/Workforce/AttendanceController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Workforce/AttendanceRegisterTest.php`

**Interfaces:**
- Consumes: `AttendanceService::forEmployeeAndMonth()` (Task 4).
- Produces: `AttendanceController::register($employeeId, Request $request)` — reads `?year=&month=` query params (default to current year/month), passes the employee and their month's attendance rows to `attendance.register`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Workforce/AttendanceRegisterTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_shows_only_the_requested_employee_and_month(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['name' => 'Suresh Yadav']);
        $otherEmployee = Employee::factory()->create(['name' => 'Other Person']);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-05', 'status' => 'present', 'marked_by' => $owner->id]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-06-05', 'status' => 'present', 'marked_by' => $owner->id]);
        Attendance::create(['employee_id' => $otherEmployee->id, 'date' => '2026-07-05', 'status' => 'present', 'marked_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('attendance.register', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('Suresh Yadav');
        $response->assertViewHas('attendanceRows', function ($rows) {
            return $rows->count() === 1;
        });
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Workforce/AttendanceRegisterTest.php`
Expected: FAIL (route `attendance.register` not defined)

- [ ] **Step 3: Implement**

```php
// app/Http/Controllers/Workforce/AttendanceController.php — add inside the class
    public function register(Request $request, $employee)
    {
        $employeeModel = $this->employeeService->find($employee);
        abort_if(! $employeeModel, 404);

        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $attendanceRows = $this->service->forEmployeeAndMonth((int) $employee, $year, $month);

        return view('attendance.register', ['employee' => $employeeModel, 'attendanceRows' => $attendanceRows, 'year' => $year, 'month' => $month]);
    }
```

```php
// routes/web.php — add to the later auth group:
    Route::get('attendance/{employee}/register', [App\Http\Controllers\Workforce\AttendanceController::class, 'register'])->name('attendance.register')->middleware('permission:attendance.view');
```

Create a minimal placeholder `resources/views/attendance/register.blade.php` for this task (`@extends('layouts.master')`, must at least `{{ $employee->name }}` so the test's `assertSee` passes) — Task 9 replaces it with the full register table.

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Workforce/AttendanceRegisterTest.php`
Expected: PASS (1/1)

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Workforce/AttendanceController.php routes/web.php resources/views/attendance/register.blade.php tests/Feature/Workforce/AttendanceRegisterTest.php
git commit -m "feat(workforce): monthly attendance register per employee"
```

---

### Task 6: Payroll Calculation Service

**Files:**
- Create: `app/Repositories/Contracts/SalaryPaymentRepositoryInterface.php`
- Create: `app/Repositories/Eloquent/EloquentSalaryPaymentRepository.php`
- Create: `app/Services/Workforce/PayrollService.php`
- Modify: `app/Providers/RepositoryServiceProvider.php`
- Test: `tests/Feature/Workforce/PayrollServiceTest.php`

**Interfaces:**
- Consumes: `AttendanceRepositoryInterface::forEmployeeAndMonth()` (Task 4), `SalaryPaymentRepositoryInterface::totalForEmployeeAndMonth()` (this task).
- Produces: `PayrollService::calculateMonthlyEarnings(Employee $employee, int $year, int $month): array` returning exactly these keys: `days_present`, `days_half`, `days_leave`, `days_absent`, `day_rate`, `base_earned`, `overtime_hours`, `overtime_earned`, `total_earned`, `total_paid`, `balance_due`. This is the exact global-constraints math: day rate = `pay_rate` for daily, `pay_rate / days_in_month` for monthly; present=1.0×, half_day=0.5×, leave/absent=0×; overtime_earned = `overtime_hours × overtime_rate_per_hour`; balance_due = `total_earned - total_paid`.

- [ ] **Step 1: Write the failing tests — these lock in exact decimal math, not approximate values**

```php
// tests/Feature/Workforce/PayrollServiceTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SalaryPayment;
use App\Models\User;
use App\Services\Workforce\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_wage_employee_earnings_with_mixed_attendance(): void
    {
        $user = User::factory()->create();
        // Daily wage 600/day, overtime 50/hour. July 2026 has 31 days.
        $employee = Employee::factory()->create(['pay_type' => 'daily', 'pay_rate' => 600, 'overtime_rate_per_hour' => 50]);
        // 2 present (1200), 1 half-day (300), 1 leave (0), 1 absent (0) = 1500 base.
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-01', 'status' => 'present', 'overtime_hours' => 2, 'marked_by' => $user->id]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-02', 'status' => 'present', 'overtime_hours' => 0, 'marked_by' => $user->id]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-03', 'status' => 'half_day', 'overtime_hours' => 0, 'marked_by' => $user->id]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-04', 'status' => 'leave', 'overtime_hours' => 0, 'marked_by' => $user->id]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-05', 'status' => 'absent', 'overtime_hours' => 0, 'marked_by' => $user->id]);
        SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-10', 'amount' => 500, 'paid_by' => $user->id]);

        $result = app(PayrollService::class)->calculateMonthlyEarnings($employee, 2026, 7);

        $this->assertEquals(2, $result['days_present']);
        $this->assertEquals(1, $result['days_half']);
        $this->assertEquals(1, $result['days_leave']);
        $this->assertEquals(1, $result['days_absent']);
        $this->assertEquals(600.00, (float) $result['day_rate']);
        $this->assertEquals(1500.00, (float) $result['base_earned']); // (2 * 600) + (1 * 300)
        $this->assertEquals(2.0, (float) $result['overtime_hours']);
        $this->assertEquals(100.00, (float) $result['overtime_earned']); // 2 * 50
        $this->assertEquals(1600.00, (float) $result['total_earned']); // 1500 + 100
        $this->assertEquals(500.00, (float) $result['total_paid']);
        $this->assertEquals(1100.00, (float) $result['balance_due']); // 1600 - 500
    }

    public function test_monthly_salary_employee_day_rate_derived_from_days_in_month(): void
    {
        $user = User::factory()->create();
        // Monthly salary 31000. April 2026 has 30 days -> day rate 1033.333...
        $employee = Employee::factory()->create(['pay_type' => 'monthly', 'pay_rate' => 31000, 'overtime_rate_per_hour' => 0]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-04-01', 'status' => 'present', 'overtime_hours' => 0, 'marked_by' => $user->id]);

        $result = app(PayrollService::class)->calculateMonthlyEarnings($employee, 2026, 4);

        $this->assertEqualsWithDelta(1033.33, (float) $result['day_rate'], 0.01);
        $this->assertEqualsWithDelta(1033.33, (float) $result['base_earned'], 0.01);
    }

    public function test_no_attendance_marked_yields_zero_earnings_not_an_error(): void
    {
        $employee = Employee::factory()->create(['pay_type' => 'daily', 'pay_rate' => 600]);

        $result = app(PayrollService::class)->calculateMonthlyEarnings($employee, 2026, 7);

        $this->assertEquals(0.00, (float) $result['total_earned']);
        $this->assertEquals(0.00, (float) $result['balance_due']);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Workforce/PayrollServiceTest.php`
Expected: FAIL (class `App\Services\Workforce\PayrollService` not found)

- [ ] **Step 3: Implement**

```php
// app/Repositories/Contracts/SalaryPaymentRepositoryInterface.php
<?php

namespace App\Repositories\Contracts;

use App\Models\SalaryPayment;
use Illuminate\Database\Eloquent\Collection;

interface SalaryPaymentRepositoryInterface
{
    public function create(array $data): SalaryPayment;

    public function forEmployeeAndMonth(int $employeeId, int $year, int $month): Collection;

    public function totalForEmployeeAndMonth(int $employeeId, int $year, int $month): float;
}
```

```php
// app/Repositories/Eloquent/EloquentSalaryPaymentRepository.php
<?php

namespace App\Repositories\Eloquent;

use App\Models\SalaryPayment;
use App\Repositories\Contracts\SalaryPaymentRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Collection;

class EloquentSalaryPaymentRepository implements SalaryPaymentRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function create(array $data): SalaryPayment
    {
        return SalaryPayment::create($data);
    }

    public function forEmployeeAndMonth(int $employeeId, int $year, int $month): Collection
    {
        return $this->tenantScope->apply(
            SalaryPayment::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
        )->orderBy('date')->get();
    }

    public function totalForEmployeeAndMonth(int $employeeId, int $year, int $month): float
    {
        return (float) $this->tenantScope->apply(
            SalaryPayment::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
        )->sum('amount');
    }
}
```

```php
// app/Services/Workforce/PayrollService.php
<?php

namespace App\Services\Workforce;

use App\Models\Employee;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Contracts\SalaryPaymentRepositoryInterface;
use Carbon\Carbon;

class PayrollService
{
    public function __construct(
        private AttendanceRepositoryInterface $attendanceRepository,
        private SalaryPaymentRepositoryInterface $salaryPaymentRepository,
    ) {
    }

    public function calculateMonthlyEarnings(Employee $employee, int $year, int $month): array
    {
        $attendanceRows = $this->attendanceRepository->forEmployeeAndMonth($employee->id, $year, $month);

        $daysPresent = $attendanceRows->where('status', 'present')->count();
        $daysHalf = $attendanceRows->where('status', 'half_day')->count();
        $daysLeave = $attendanceRows->where('status', 'leave')->count();
        $daysAbsent = $attendanceRows->where('status', 'absent')->count();
        $overtimeHours = (float) $attendanceRows->sum('overtime_hours');

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $dayRate = $employee->pay_type === 'daily'
            ? (float) $employee->pay_rate
            : (float) $employee->pay_rate / $daysInMonth;

        $baseEarned = ($daysPresent * $dayRate) + ($daysHalf * $dayRate * 0.5);
        $overtimeEarned = $overtimeHours * (float) $employee->overtime_rate_per_hour;
        $totalEarned = $baseEarned + $overtimeEarned;

        $totalPaid = $this->salaryPaymentRepository->totalForEmployeeAndMonth($employee->id, $year, $month);

        return [
            'days_present' => $daysPresent,
            'days_half' => $daysHalf,
            'days_leave' => $daysLeave,
            'days_absent' => $daysAbsent,
            'day_rate' => round($dayRate, 2),
            'base_earned' => round($baseEarned, 2),
            'overtime_hours' => $overtimeHours,
            'overtime_earned' => round($overtimeEarned, 2),
            'total_earned' => round($totalEarned, 2),
            'total_paid' => round($totalPaid, 2),
            'balance_due' => round($totalEarned - $totalPaid, 2),
        ];
    }
}
```

```php
// app/Providers/RepositoryServiceProvider.php — add import + binding
use App\Repositories\Contracts\SalaryPaymentRepositoryInterface;
use App\Repositories\Eloquent\EloquentSalaryPaymentRepository;
// ... inside register():
$this->app->bind(SalaryPaymentRepositoryInterface::class, EloquentSalaryPaymentRepository::class);
```

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Workforce/PayrollServiceTest.php`
Expected: PASS (3/3)

- [ ] **Step 5: Commit**

```bash
git add app/Repositories/Contracts/SalaryPaymentRepositoryInterface.php app/Repositories/Eloquent/EloquentSalaryPaymentRepository.php app/Services/Workforce/PayrollService.php app/Providers/RepositoryServiceProvider.php tests/Feature/Workforce/PayrollServiceTest.php
git commit -m "feat(workforce): payroll calculation service with exact attendance-based math"
```

---

### Task 7: Salary Payment Ledger (Controller + Routes)

**Files:**
- Create: `app/Http/Controllers/Workforce/PayrollController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Workforce/PayrollControllerTest.php`

**Interfaces:**
- Consumes: `PayrollService::calculateMonthlyEarnings()` (Task 6), `SalaryPaymentRepositoryInterface::create()`/`forEmployeeAndMonth()` (Task 6).
- Produces: `PayrollController::show($employeeId, Request $request)` (reads `?year=&month=`, renders the earnings breakdown + payment ledger), `PayrollController::storePayment(Request $request, $employeeId)` (adds a salary payment).

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Workforce/PayrollControllerTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_payroll_view_shows_earnings_and_balance(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['pay_type' => 'daily', 'pay_rate' => 500]);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-01', 'status' => 'present', 'marked_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('500');
    }

    public function test_owner_adds_a_salary_payment(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();

        $response = $this->actingAs($owner)->post(route('employees.payments.store', $employee->id), [
            'date' => '2026-07-15',
            'amount' => 2000,
            'note' => 'Mid-month advance',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('salary_payments', ['employee_id' => $employee->id, 'amount' => 2000, 'note' => 'Mid-month advance']);
    }

    public function test_worker_cannot_view_payroll(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $employee = Employee::factory()->create();

        $response = $this->actingAs($worker)->get(route('employees.payroll', $employee->id));

        $response->assertForbidden();
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Workforce/PayrollControllerTest.php`
Expected: FAIL (route `employees.payroll` not defined)

- [ ] **Step 3: Implement**

```php
// app/Http/Controllers/Workforce/PayrollController.php
<?php

namespace App\Http\Controllers\Workforce;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\SalaryPaymentRepositoryInterface;
use App\Services\Workforce\EmployeeService;
use App\Services\Workforce\PayrollService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(
        private EmployeeService $employeeService,
        private PayrollService $payrollService,
        private SalaryPaymentRepositoryInterface $paymentRepository,
    ) {
    }

    public function show(Request $request, $employee)
    {
        $employeeModel = $this->employeeService->find($employee);
        abort_if(! $employeeModel, 404);

        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $earnings = $this->payrollService->calculateMonthlyEarnings($employeeModel, $year, $month);
        $payments = $this->paymentRepository->forEmployeeAndMonth((int) $employee, $year, $month);

        return view('payroll.show', ['employee' => $employeeModel, 'earnings' => $earnings, 'payments' => $payments, 'year' => $year, 'month' => $month]);
    }

    public function storePayment(Request $request, $employee)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);
        $employeeModel = $this->employeeService->find($employee);
        abort_if(! $employeeModel, 404);

        $this->paymentRepository->create([
            'employee_id' => $employeeModel->id,
            'date' => $data['date'],
            'amount' => $data['amount'],
            'note' => $data['note'] ?? null,
            'paid_by' => $request->user()->id,
        ]);

        return redirect()->route('employees.payroll', ['employee' => $employeeModel->id, 'year' => date('Y', strtotime($data['date'])), 'month' => date('n', strtotime($data['date']))])->with('success', 'Payment recorded.');
    }
}
```

```php
// routes/web.php — add to the later auth group:
    Route::get('employees/{employee}/payroll', [App\Http\Controllers\Workforce\PayrollController::class, 'show'])->name('employees.payroll')->middleware('permission:payroll.view');
    Route::post('employees/{employee}/payments', [App\Http\Controllers\Workforce\PayrollController::class, 'storePayment'])->name('employees.payments.store')->middleware('permission:payroll.manage-payments');
```

Create a minimal placeholder `resources/views/payroll/show.blade.php` for this task (`@extends('layouts.master')`, must render `{{ $earnings['day_rate'] }}` or similar so the test's `assertSee('500')` passes) — Task 9 replaces it with the full payroll UI.

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Workforce/PayrollControllerTest.php`
Expected: PASS (3/3)

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Workforce/PayrollController.php routes/web.php resources/views/payroll/show.blade.php tests/Feature/Workforce/PayrollControllerTest.php
git commit -m "feat(workforce): payroll view and salary payment ledger endpoints"
```

---

### Task 8: Employee UI (List, Create, Edit)

**Files:**
- Create: `resources/views/employees/index.blade.php`
- Modify: `resources/views/employees/create.blade.php` (replace Task 2's placeholder)
- Create: `resources/views/employees/edit.blade.php`
- Test: `tests/Feature/Workforce/EmployeeUiTest.php`

**Interfaces:**
- Consumes: `employees.index`/`employees.create`/`employees.store`/`employees.edit`/`employees.update`/`employees.deactivate`/`employees.documents.store` routes (Tasks 2-3). `EmployeeController::index()` passes `$employees`; `edit()` passes `$employee`.

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Workforce/EmployeeUiTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_employees_with_pay_type(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        Employee::factory()->create(['name' => 'Vikram Singh', 'pay_type' => 'daily']);

        $response = $this->actingAs($owner)->get(route('employees.index'));

        $response->assertOk();
        $response->assertSee('Vikram Singh');
    }

    public function test_create_form_renders(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('employees.create'));

        $response->assertOk();
    }

    public function test_edit_form_shows_existing_values_and_document_upload(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create(['name' => 'Deepak Verma']);

        $response = $this->actingAs($owner)->get(route('employees.edit', $employee->id));

        $response->assertOk();
        $response->assertSee('Deepak Verma');
        $response->assertSee(route('employees.documents.store', $employee->id), false);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Workforce/EmployeeUiTest.php`
Expected: FAIL (`test_edit_form_shows_existing_values_and_document_upload` — route `employees.edit` works from Task 2, but the placeholder view lacks the employee's name/document form)

- [ ] **Step 3: Implement**

```blade
{{-- resources/views/employees/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-ui.data-table-card title="Employees" :create-route="route('employees.create')" create-label="Add Employee">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Pay Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->phone }}</td>
                        <td>{{ $employee->department }}</td>
                        <td>{{ ucfirst($employee->pay_type) }}</td>
                        <td>
                            <x-ui.status-badge
                                :status="$employee->is_active ? 'Active' : 'Inactive'"
                                :variant="$employee->is_active ? 'success' : 'secondary'"
                                icon="{{ $employee->is_active ? 'ri-checkbox-circle-line' : 'ri-close-circle-line' }}" />
                        </td>
                        <td>
                            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            <a href="{{ route('attendance.register', $employee->id) }}" class="btn btn-sm btn-secondary">Attendance</a>
                            <a href="{{ route('employees.payroll', $employee->id) }}" class="btn btn-sm btn-success">Payroll</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state icon="ri-team-line" message="No employees added yet." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.data-table-card>
</div>
@endsection
```

```blade
{{-- resources/views/employees/create.blade.php — replaces Task 2's placeholder --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Add Employee</h5>
            <form action="{{ route('employees.store') }}" method="POST">
                @csrf
                @include('employees._form')
                <x-ui.button variant="success" type="submit" icon="ri-save-line" ariaLabel="Save employee">Save</x-ui.button>
            </form>
        </div>
    </div>
</div>
@endsection
```

```blade
{{-- resources/views/employees/_form.blade.php — shared field partial for create/edit --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-name">Name</label>
        <input id="employee-name" type="text" name="name" class="form-control" value="{{ old('name', $employee->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-phone">Phone</label>
        <input id="employee-phone" type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-email">Email</label>
        <input id="employee-email" type="email" name="email" class="form-control" value="{{ old('email', $employee->email ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-department">Department</label>
        <input id="employee-department" type="text" name="department" class="form-control" value="{{ old('department', $employee->department ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-designation">Designation</label>
        <input id="employee-designation" type="text" name="designation" class="form-control" value="{{ old('designation', $employee->designation ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-joining-date">Joining Date</label>
        <input id="employee-joining-date" type="date" name="joining_date" class="form-control" value="{{ old('joining_date', isset($employee) ? $employee->joining_date->toDateString() : '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-address">Address</label>
        <textarea id="employee-address" name="address" class="form-control">{{ old('address', $employee->address ?? '') }}</textarea>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-emergency-name">Emergency Contact Name</label>
        <input id="employee-emergency-name" type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $employee->emergency_contact_name ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-emergency-phone">Emergency Contact Phone</label>
        <input id="employee-emergency-phone" type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-pay-type">Pay Type</label>
        <select id="employee-pay-type" name="pay_type" class="form-select" required>
            <option value="daily" @selected(old('pay_type', $employee->pay_type ?? '') === 'daily')>Daily Wage</option>
            <option value="monthly" @selected(old('pay_type', $employee->pay_type ?? '') === 'monthly')>Monthly Salary</option>
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-pay-rate">Pay Rate (₹)</label>
        <input id="employee-pay-rate" type="number" step="0.01" name="pay_rate" class="form-control" value="{{ old('pay_rate', $employee->pay_rate ?? '') }}" required>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-ot-rate">Overtime Rate (₹/hour)</label>
        <input id="employee-ot-rate" type="number" step="0.01" name="overtime_rate_per_hour" class="form-control" value="{{ old('overtime_rate_per_hour', $employee->overtime_rate_per_hour ?? 0) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-bank-holder">Bank Account Holder</label>
        <input id="employee-bank-holder" type="text" name="bank_account_holder_name" class="form-control" value="{{ old('bank_account_holder_name', $employee->bank_account_holder_name ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-bank-account">Bank Account Number</label>
        <input id="employee-bank-account" type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $employee->bank_account_number ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-bank-ifsc">Bank IFSC</label>
        <input id="employee-bank-ifsc" type="text" name="bank_ifsc" class="form-control" value="{{ old('bank_ifsc', $employee->bank_ifsc ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-bank-name">Bank Name</label>
        <input id="employee-bank-name" type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $employee->bank_name ?? '') }}">
    </div>
</div>
```

```blade
{{-- resources/views/employees/edit.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Edit Employee — {{ $employee->name }}</h5>
            <form action="{{ route('employees.update', $employee->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('employees._form')
                <x-ui.button variant="success" type="submit" icon="ri-save-line" ariaLabel="Save employee">Save</x-ui.button>
            </form>

            @if($employee->is_active)
                <form action="{{ route('employees.deactivate', $employee->id) }}" method="POST" class="mt-2 d-inline">
                    @csrf
                    <x-ui.button variant="danger" type="submit" icon="ri-user-unfollow-line" ariaLabel="Deactivate employee">Deactivate</x-ui.button>
                </form>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">ID Documents</h5>
            <div class="row g-2 mb-3">
                @forelse($employee->documents as $document)
                    <div class="col-md-3">
                        <div class="border rounded p-2">
                            <span class="badge bg-info-subtle text-info text-uppercase">{{ str_replace('_', ' ', $document->document_type) }}</span>
                            <p class="small mb-0">{{ $document->document_number }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-12"><x-ui.empty-state icon="ri-file-list-3-line" message="No documents uploaded yet." /></div>
                @endforelse
            </div>
            <form action="{{ route('employees.documents.store', $employee->id) }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 flex-wrap align-items-end">
                @csrf
                <div>
                    <label class="form-label" for="doc-type">Document Type</label>
                    <select id="doc-type" name="document_type" class="form-select" required>
                        <option value="aadhar">Aadhar</option>
                        <option value="pan">PAN</option>
                        <option value="driving_license">Driving License</option>
                        <option value="voter_id">Voter ID</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="doc-number">Document Number (optional)</label>
                    <input id="doc-number" type="text" name="document_number" class="form-control">
                </div>
                <div>
                    <label class="form-label" for="doc-file">File (image or PDF)</label>
                    <input id="doc-file" type="file" name="document" accept="image/*,application/pdf" class="form-control" required>
                </div>
                <x-ui.button variant="primary" type="submit" icon="ri-upload-line" ariaLabel="Upload document">Upload</x-ui.button>
            </form>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Workforce/EmployeeUiTest.php`
Expected: PASS (3/3)

- [ ] **Step 5: Commit**

```bash
git add resources/views/employees/index.blade.php resources/views/employees/create.blade.php resources/views/employees/edit.blade.php resources/views/employees/_form.blade.php tests/Feature/Workforce/EmployeeUiTest.php
git commit -m "feat(workforce): employee list, create/edit forms, and ID document management UI"
```

---

### Task 9: Attendance + Payroll UI

**Files:**
- Modify: `resources/views/attendance/mark.blade.php` (replace Task 4's placeholder)
- Modify: `resources/views/attendance/register.blade.php` (replace Task 5's placeholder)
- Modify: `resources/views/payroll/show.blade.php` (replace Task 7's placeholder)
- Test: `tests/Feature/Workforce/AttendancePayrollUiTest.php`

**Interfaces:**
- Consumes: `attendance.mark`/`attendance.store` (Task 4), `attendance.register` (Task 5), `employees.payroll`/`employees.payments.store` (Task 7). `AttendanceController::mark()` passes `$date`, `$employees` (active only), `$existing` (keyed by employee_id); `register()` passes `$employee`, `$attendanceRows`, `$year`, `$month`; `PayrollController::show()` passes `$employee`, `$earnings` (array from `PayrollService`), `$payments`, `$year`, `$month`.

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Workforce/AttendancePayrollUiTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SalaryPayment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendancePayrollUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_page_lists_active_employees_for_bulk_entry(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        Employee::factory()->create(['name' => 'Active Worker', 'is_active' => true]);
        Employee::factory()->create(['name' => 'Inactive Worker', 'is_active' => false]);

        $response = $this->actingAs($owner)->get(route('attendance.mark'));

        $response->assertOk();
        $response->assertSee('Active Worker');
        $response->assertDontSee('Inactive Worker');
    }

    public function test_register_page_shows_the_months_marked_days(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-07-10', 'status' => 'present', 'marked_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('attendance.register', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('present');
    }

    public function test_payroll_page_shows_ledger_and_add_payment_form(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();
        SalaryPayment::create(['employee_id' => $employee->id, 'date' => '2026-07-10', 'amount' => 1500, 'note' => 'Advance', 'paid_by' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('employees.payroll', ['employee' => $employee->id, 'year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('1500');
        $response->assertSee('Advance');
        $response->assertSee(route('employees.payments.store', $employee->id), false);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Workforce/AttendancePayrollUiTest.php`
Expected: FAIL (placeholder views don't render employee names/attendance status/payment ledger)

- [ ] **Step 3: Implement**

```blade
{{-- resources/views/attendance/mark.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Mark Attendance</h5>
                <form method="GET" action="{{ route('attendance.mark') }}" class="d-flex gap-2">
                    <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
                </form>
            </div>

            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Overtime Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $index => $employee)
                            @php $existingRow = $existing->get($employee->id); @endphp
                            <tr>
                                <td>
                                    {{ $employee->name }}
                                    <input type="hidden" name="rows[{{ $index }}][employee_id]" value="{{ $employee->id }}">
                                </td>
                                <td>
                                    <select name="rows[{{ $index }}][status]" class="form-select">
                                        <option value="present" @selected(($existingRow->status ?? 'present') === 'present')>Present</option>
                                        <option value="absent" @selected(($existingRow->status ?? '') === 'absent')>Absent</option>
                                        <option value="half_day" @selected(($existingRow->status ?? '') === 'half_day')>Half Day</option>
                                        <option value="leave" @selected(($existingRow->status ?? '') === 'leave')>Leave</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.5" min="0" name="rows[{{ $index }}][overtime_hours]" class="form-control" value="{{ $existingRow->overtime_hours ?? 0 }}">
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-ui.empty-state icon="ri-team-line" message="No active employees to mark." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($employees->isNotEmpty())
                    <x-ui.button variant="success" type="submit" icon="ri-checkbox-circle-line" ariaLabel="Save attendance">Save Attendance</x-ui.button>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
```

```blade
{{-- resources/views/attendance/register.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $employee->name }} — Attendance Register ({{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }})</h5>
            <table class="table table-bordered">
                <thead><tr><th>Date</th><th>Status</th><th>Overtime Hours</th></tr></thead>
                <tbody>
                    @forelse($attendanceRows as $row)
                        <tr>
                            <td>{{ $row->date->toDateString() }}</td>
                            <td>{{ str_replace('_', ' ', $row->status) }}</td>
                            <td>{{ $row->overtime_hours }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-ui.empty-state icon="ri-calendar-line" message="No attendance marked for this month yet." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
```

```blade
{{-- resources/views/payroll/show.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $employee->name }} — Payroll ({{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }})</h5>
            <div class="row g-2">
                <div class="col-md-3"><p class="text-muted mb-0">Days Present</p><h5>{{ $earnings['days_present'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Half Days</p><h5>{{ $earnings['days_half'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Leave</p><h5>{{ $earnings['days_leave'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Absent</p><h5>{{ $earnings['days_absent'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Day Rate (₹)</p><h5>{{ $earnings['day_rate'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Base Earned (₹)</p><h5>{{ $earnings['base_earned'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Overtime Earned (₹)</p><h5>{{ $earnings['overtime_earned'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Total Earned (₹)</p><h5>{{ $earnings['total_earned'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Total Paid (₹)</p><h5>{{ $earnings['total_paid'] }}</h5></div>
                <div class="col-md-3"><p class="text-muted mb-0">Balance Due (₹)</p><h4 class="text-danger">{{ $earnings['balance_due'] }}</h4></div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Payments This Month</h5>
            <table class="table table-bordered">
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
            </table>

            <form action="{{ route('employees.payments.store', $employee->id) }}" method="POST" class="d-flex gap-2 flex-wrap align-items-end mt-3">
                @csrf
                <div>
                    <label class="form-label" for="payment-date">Date</label>
                    <input id="payment-date" type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div>
                    <label class="form-label" for="payment-amount">Amount (₹)</label>
                    <input id="payment-amount" type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div>
                    <label class="form-label" for="payment-note">Note</label>
                    <input id="payment-note" type="text" name="note" class="form-control">
                </div>
                <x-ui.button variant="success" type="submit" icon="ri-add-line" ariaLabel="Record payment">Record Payment</x-ui.button>
            </form>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Workforce/AttendancePayrollUiTest.php`
Expected: PASS (3/3)

- [ ] **Step 5: Commit**

```bash
git add resources/views/attendance/mark.blade.php resources/views/attendance/register.blade.php resources/views/payroll/show.blade.php tests/Feature/Workforce/AttendancePayrollUiTest.php
git commit -m "feat(workforce): bulk attendance entry, monthly register, and payroll UI"
```

---

### Task 10: Sidebar Integration + Final Verification

**Files:**
- Modify: `resources/views/layouts/sidebar.blade.php`
- Test: `tests/Feature/Workforce/WorkforceSidebarTest.php`

**Interfaces:**
- Consumes: `employees.index`, `attendance.mark` routes.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Workforce/WorkforceSidebarTest.php
<?php

namespace Tests\Feature\Workforce;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_employees_and_attendance_links(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');

        $response = $this->actingAs($owner)->get(route('employees.index'));

        $response->assertOk();
        $response->assertSee(route('employees.index'), false);
        $response->assertSee(route('attendance.mark'), false);
    }

    public function test_worker_does_not_see_employees_link(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertDontSee(route('employees.index'), false);
    }
}
```

- [ ] **Step 2: Run, confirm failure**

Run: `php artisan test tests/Feature/Workforce/WorkforceSidebarTest.php`
Expected: FAIL (sidebar has no Employees/Attendance links yet)

- [ ] **Step 3: Implement**

```blade
{{-- resources/views/layouts/sidebar.blade.php — add near the other @can-gated menu items,
     e.g. after the Machines link added by an earlier feature --}}
@can('employees.view')
<li class="nav-item">
    <a class="nav-link menu-link" href="{{ route('employees.index') }}">
        <i class="ri-team-line"></i> <span>Employees</span>
    </a>
</li>
@endcan
@can('attendance.manage')
<li class="nav-item">
    <a class="nav-link menu-link" href="{{ route('attendance.mark') }}">
        <i class="ri-calendar-check-line"></i> <span>Attendance</span>
    </a>
</li>
@endcan
```

- [ ] **Step 4: Run tests, confirm pass**

Run: `php artisan test tests/Feature/Workforce/WorkforceSidebarTest.php`
Expected: PASS (2/2)

- [ ] **Step 5: Run the FULL suite — this is the last task, no regressions across the whole app**

Run: `php artisan test`
Expected: all pass, including every prior workforce test and the pre-existing suite.

- [ ] **Step 6: Commit**

```bash
git add resources/views/layouts/sidebar.blade.php tests/Feature/Workforce/WorkforceSidebarTest.php
git commit -m "feat(workforce): add Employees/Attendance sidebar navigation"
```
