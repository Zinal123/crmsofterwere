# Dynamic Role Matrix (RBAC) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give this Laravel 10 CRM a database-backed, dynamically-configurable role/permission system (Owner + Worker seed roles, more creatable via UI), an admin screen to manage roles/permissions and users, and route-level enforcement across every existing module — replacing the current state where every logged-in user has identical unrestricted access.

**Architecture:** `spatie/laravel-permission` (no Teams feature) as the RBAC foundation, wired into the existing Controller → Service → Repository pattern via a new `Admin` domain (`RoleController`, `UserController`). Every existing route gets `permission:` middleware matching a fixed 24-permission list grouped by module. Sidebar links are wrapped in `@can(...)`.

**Tech Stack:** Laravel 10, PHP 8.1+, spatie/laravel-permission ^6.0, MySQL (live) + sqlite (`.env.testing`, `RefreshDatabase`), Blade + Bootstrap 5 (existing Velzon template conventions), jQuery for AJAX (matching existing app patterns, e.g. `quantityupdate`).

## Global Constraints

- Follow `docs/DEVELOPMENT-STANDARDS.md`: Controller → Service → Repository layering, `TenantScope` on every repository read, test-first-then-refactor, preserve-and-document (not silently fix) unrelated bugs, migrations only (no live `ALTER TABLE`), full verification checklist per task, detailed commit messages.
- Spec: `docs/superpowers/specs/2026-07-17-role-matrix-design.md` — this plan implements it exactly. No Teams/multi-tenancy, no job-tracking permissions, no forced password change, no email-based password delivery, single role per user.
- Permission list (exact 24, grouped by module) — the single source of truth used throughout this plan:
  ```
  dashboard.view
  products.view, products.create, products.delete, products.manage-config
  inventory.view, inventory.create, inventory.update
  invoices.view, invoices.create, invoices.view-details, invoices.record-payment
  vendors.view
  payment-history.view
  quotations.view, quotations.create, quotations.download-pdf
  admin.manage-roles
  admin.manage-users
  ```
- Owner role: all 24 permissions. Worker role: zero permissions (job-tracking permissions attached to it in a future, separate feature).
- `RefreshDatabase` + spatie/laravel-permission has a known gotcha: Spatie caches loaded permissions across the whole PHPUnit process, but `RefreshDatabase` rolls back per-test via a DB transaction — stale cache from an earlier test can leak into a later one. Every task that touches permissions resets the cache in `setUp()` (Task 1 adds this once, globally, to the base `TestCase`).

---

### Task 1: Install spatie/laravel-permission, add `is_active`, wire the User model

**Files:**
- Modify: `composer.json` (via `composer require`)
- Create (published by package): `config/permission.php`
- Create (published by package): `database/migrations/2026_07_18_000001_create_permission_tables.php`
- Create: `database/migrations/2026_07_18_000002_add_is_active_to_users_table.php`
- Modify: `app/Models/User.php`
- Modify: `tests/TestCase.php`
- Test: `tests/Feature/UserModelRolesTest.php`

**Interfaces:**
- Produces: `User` model gains `HasRoles` trait (from Spatie — provides `assignRole()`, `hasRole()`, `can()`, `getPermissionsViaRoles()`) and `is_active` (bool, default `true`, in `$fillable` and `$casts`). Every later task relies on `$user->hasRole('Owner')`, `$user->can('invoices.view')`, and `$user->is_active`.

- [ ] **Step 1: Install the package**

Run:
```bash
cd "/Users/meetpatel/_public_html (1)"
composer require spatie/laravel-permission:^6.0
```
Expected: composer.json and composer.lock updated, package installed under vendor/.

- [ ] **Step 2: Publish the package's migration and config**

Run:
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```
Expected: creates `config/permission.php` and a migration file under `database/migrations/` timestamped with today's date (e.g. `2026_07_18_HHMMSS_create_permission_tables.php` — the exact seconds depend on when the command runs).

Rename it to a fixed, predictable name so it sorts after `2026_07_15_165539_add_invetry_product_id_index.php` and before the `is_active` migration this task adds next, matching the chronological convention in `docs/DEVELOPMENT-STANDARDS.md` §4:
```bash
mv database/migrations/*_create_permission_tables.php database/migrations/2026_07_18_000001_create_permission_tables.php
```
Expected: `ls database/migrations/2026_07_18_000001_create_permission_tables.php` shows the renamed file.

- [ ] **Step 3: Write the failing test for User + roles**

Create `tests/Feature/UserModelRolesTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserModelRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_assigned_a_role_and_checked(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('Owner');

        $user->assignRole('Owner');

        $this->assertTrue($user->hasRole('Owner'));
    }

    public function test_user_permission_check_reflects_role_permissions(): void
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('Owner');
        Permission::findOrCreate('invoices.view');
        $role->givePermissionTo('invoices.view');
        $user->assignRole('Owner');

        $this->assertTrue($user->can('invoices.view'));
        $this->assertFalse($user->can('invoices.create'));
    }

    public function test_is_active_defaults_to_true(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->is_active);
    }
}
```

- [ ] **Step 4: Run test to verify it fails**

Run: `php artisan test --filter=UserModelRolesTest`
Expected: FAIL — `Call to undefined method App\Models\User::assignRole()` (trait not added yet) and/or `Unknown column 'is_active'`.

- [ ] **Step 5: Add the `is_active` migration**

Create `database/migrations/2026_07_18_000002_add_is_active_to_users_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
```

- [ ] **Step 6: Update the User model**

Modify `app/Models/User.php` — add the `HasRoles` trait and `is_active` to `$fillable`/`$casts`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
```

- [ ] **Step 7: Reset the Spatie permission cache between tests (base TestCase)**

Modify `tests/TestCase.php`:
```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
```

- [ ] **Step 8: Run migrations against the test DB and re-run the test**

Run:
```bash
php artisan test --filter=UserModelRolesTest
```
Expected: PASS (all 3 tests) — `RefreshDatabase` picks up both the new `is_active` migration and the published Spatie migration automatically.

- [ ] **Step 9: Verify migrations also run clean standalone**

Run:
```bash
php artisan migrate:fresh --env=testing --database=sqlite
```
Expected: all migrations (including the two new ones) run without error, ending in `Nothing to migrate` on a second run.

- [ ] **Step 10: Commit**

```bash
git add composer.json composer.lock config/permission.php database/migrations/2026_07_18_000001_create_permission_tables.php database/migrations/2026_07_18_000002_add_is_active_to_users_table.php app/Models/User.php tests/TestCase.php tests/Feature/UserModelRolesTest.php
git commit -m "Add spatie/laravel-permission and is_active column to users

Foundation for the dynamic role matrix: HasRoles trait on User,
is_active flag for deactivating a worker without deleting their
records, and a base TestCase permission-cache reset to avoid a known
RefreshDatabase + Spatie caching gotcha in later tests."
```

---

### Task 2: `RolesAndPermissionsSeeder` + factory Owner-role hook

**Files:**
- Create: `database/seeders/RolesAndPermissionsSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Modify: `database/factories/UserFactory.php`
- Test: `tests/Feature/RolesAndPermissionsSeederTest.php`

**Interfaces:**
- Consumes: `Role`/`Permission` models from Task 1's installed package.
- Produces: `RolesAndPermissionsSeeder::PERMISSIONS` (public array constant, the single source of truth for the 24 permission names — Task 4/5/6 route middleware and Task 4's matrix UI both read from this instead of re-declaring the list). `RolesAndPermissionsSeeder::run()` is idempotent (safe to call repeatedly). `User::factory()` now creates users with the `Owner` role and every permission already assigned (matches current reality — one access level today), via a `configure()` afterCreating hook.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/RolesAndPermissionsSeederTest.php`:
```php
<?php

namespace Tests\Feature;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_all_24_permissions(): void
    {
        (new RolesAndPermissionsSeeder())->run();

        $this->assertCount(24, Permission::all());
        $this->assertTrue(Permission::where('name', 'invoices.view')->exists());
        $this->assertTrue(Permission::where('name', 'admin.manage-roles')->exists());
    }

    public function test_owner_role_gets_every_permission(): void
    {
        (new RolesAndPermissionsSeeder())->run();

        $owner = Role::findByName('Owner');

        $this->assertCount(24, $owner->permissions);
    }

    public function test_worker_role_gets_no_permissions(): void
    {
        (new RolesAndPermissionsSeeder())->run();

        $worker = Role::findByName('Worker');

        $this->assertCount(0, $worker->permissions);
    }

    public function test_seeder_is_idempotent(): void
    {
        (new RolesAndPermissionsSeeder())->run();
        (new RolesAndPermissionsSeeder())->run();

        $this->assertCount(24, Permission::all());
        $this->assertCount(2, Role::all());
    }

    public function test_new_factory_user_has_owner_role_and_full_permissions(): void
    {
        $user = \App\Models\User::factory()->create();

        $this->assertTrue($user->hasRole('Owner'));
        $this->assertTrue($user->can('invoices.view'));
        $this->assertTrue($user->can('admin.manage-users'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=RolesAndPermissionsSeederTest`
Expected: FAIL — `Class "Database\Seeders\RolesAndPermissionsSeeder" not found`.

- [ ] **Step 3: Create the seeder**

Create `database/seeders/RolesAndPermissionsSeeder.php`:
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'dashboard.view',
        'products.view', 'products.create', 'products.delete', 'products.manage-config',
        'inventory.view', 'inventory.create', 'inventory.update',
        'invoices.view', 'invoices.create', 'invoices.view-details', 'invoices.record-payment',
        'vendors.view',
        'payment-history.view',
        'quotations.view', 'quotations.create', 'quotations.download-pdf',
        'admin.manage-roles',
        'admin.manage-users',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission);
        }

        $owner = Role::findOrCreate('Owner');
        $owner->syncPermissions(self::PERMISSIONS);

        Role::findOrCreate('Worker');
    }
}
```

- [ ] **Step 4: Wire it into `DatabaseSeeder`**

Modify `database/seeders/DatabaseSeeder.php` — add a call to the new seeder in `run()` (read the existing file first to preserve whatever else it already calls, then add):
```php
$this->call(RolesAndPermissionsSeeder::class);
```
(add the corresponding `use Database\Seeders\RolesAndPermissionsSeeder;` — omit if `DatabaseSeeder` is itself in the `Database\Seeders` namespace, in which case no import is needed).

- [ ] **Step 5: Add the factory hook**

Modify `database/factories/UserFactory.php`:
```php
<?php

namespace Database\Factories;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'is_active' => true,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            (new RolesAndPermissionsSeeder())->run();
            $user->assignRole('Owner');
        });
    }

    public function unverified()
    {
        return $this->state(function () {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --filter=RolesAndPermissionsSeederTest`
Expected: PASS (all 5 tests).

- [ ] **Step 7: Run the full suite to check for regressions**

Run: `php artisan test`
Expected: all previously-passing tests (24 from before this feature) still pass — every existing test's `User::factory()->create()` now transparently gets the Owner role with full permissions, which doesn't change any existing route's behavior since no route has permission middleware yet (added in Task 6).

- [ ] **Step 8: Commit**

```bash
git add database/seeders/RolesAndPermissionsSeeder.php database/seeders/DatabaseSeeder.php database/factories/UserFactory.php tests/Feature/RolesAndPermissionsSeederTest.php
git commit -m "Add RolesAndPermissionsSeeder; UserFactory now assigns Owner role

Single source of truth for the 24-permission list. UserFactory's
afterCreating hook keeps every existing and new test working once
permission middleware is added in a later task, without each test
needing to know about roles."
```

---

### Task 3: `is_active` login gate

**Files:**
- Modify: `app/Http/Controllers/Auth/LoginController.php`
- Test: `tests/Feature/Auth/LoginActiveCheckTest.php`

**Interfaces:**
- Consumes: `User::is_active` (Task 1).
- Produces: a deactivated user cannot stay logged in — `authenticated()` hook logs them out and redirects to `/login` with a flashed error.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Auth/LoginActiveCheckTest.php`:
```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginActiveCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_log_in(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/');
    }

    public function test_deactivated_user_is_logged_out_and_redirected_to_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LoginActiveCheckTest`
Expected: `test_active_user_can_log_in` passes, `test_deactivated_user_is_logged_out_and_redirected_to_login` FAILS (currently logs in regardless of `is_active`).

- [ ] **Step 3: Add the `authenticated()` hook**

Modify `app/Http/Controllers/Auth/LoginController.php`:
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('error', 'Your account has been deactivated. Contact an administrator.');
        }

        return redirect()->intended($this->redirectTo);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=LoginActiveCheckTest`
Expected: PASS (both tests).

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Auth/LoginController.php tests/Feature/Auth/LoginActiveCheckTest.php
git commit -m "Block login for deactivated users

Owner can deactivate a worker who leaves (is_active=false) without
deleting their account or historical records; they now get logged
out immediately on login attempt with a clear message instead of
silently getting full access."
```

---

### Task 4: Role repository/service/controller + matrix UI

**Files:**
- Create: `app/Repositories/Contracts/RoleRepositoryInterface.php`
- Create: `app/Repositories/Eloquent/EloquentRoleRepository.php`
- Create: `app/Services/Admin/RoleService.php`
- Create: `app/Http/Controllers/Admin/RoleController.php`
- Create: `resources/views/admin/roles.blade.php`
- Modify: `app/Providers/RepositoryServiceProvider.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/RoleTest.php`

**Interfaces:**
- Consumes: `RolesAndPermissionsSeeder::PERMISSIONS` (Task 2) for the matrix's row list.
- Produces: `RoleService::listRolesWithPermissions(): Collection`, `RoleService::createRole(string $name): Role`, `RoleService::deleteRole($id): void`, `RoleService::togglePermission($roleId, string $permissionName): bool` (returns the new granted/revoked state) — used by Task 6's sidebar/tests as the pattern reference, not directly consumed elsewhere.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/RoleTest.php`:
```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_roles_page_renders_with_the_matrix(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->get(route('admin.roles.index'));

        $response->assertOk();
        $response->assertSee('Owner');
        $response->assertSee('Worker');
        $response->assertSee('invoices.view');
    }

    public function test_owner_can_create_a_new_role(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->post(route('admin.roles.store'), ['name' => 'Manager']);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'Manager']);
    }

    public function test_owner_can_toggle_a_permission_on_a_role(): void
    {
        $owner = User::factory()->create();
        $worker = Role::findByName('Worker');

        $response = $this->actingAs($owner)->postJson(route('admin.roles.togglePermission', $worker->id), [
            'permission' => 'invoices.view',
        ]);

        $response->assertOk();
        $response->assertJson(['granted' => true]);
        $this->assertTrue($worker->fresh()->hasPermissionTo('invoices.view'));

        $response2 = $this->actingAs($owner)->postJson(route('admin.roles.togglePermission', $worker->id), [
            'permission' => 'invoices.view',
        ]);
        $response2->assertJson(['granted' => false]);
        $this->assertFalse($worker->fresh()->hasPermissionTo('invoices.view'));
    }

    public function test_owner_can_delete_a_role_with_no_users_assigned(): void
    {
        $owner = User::factory()->create();
        Role::create(['name' => 'Temp']);

        $response = $this->actingAs($owner)->delete(route('admin.roles.destroy', Role::findByName('Temp')->id));

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseMissing('roles', ['name' => 'Temp']);
    }

    public function test_user_without_permission_cannot_reach_roles_page(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']); // overrides the factory's default Owner role

        $response = $this->actingAs($worker)->get(route('admin.roles.index'));

        $response->assertStatus(403);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=Admin\\\\RoleTest`
Expected: FAIL — route `admin.roles.index` not defined.

- [ ] **Step 3: Create the repository interface**

Create `app/Repositories/Contracts/RoleRepositoryInterface.php`:
```php
<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface
{
    public function allWithPermissions(): Collection;

    public function find($id): ?Role;

    public function create(string $name): Role;

    public function delete($id): void;
}
```

- [ ] **Step 4: Create the Eloquent implementation**

Create `app/Repositories/Eloquent/EloquentRoleRepository.php`:
```php
<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function allWithPermissions(): Collection
    {
        return Role::with('permissions')->orderBy('name')->get();
    }

    public function find($id): ?Role
    {
        return Role::find($id);
    }

    public function create(string $name): Role
    {
        return Role::findOrCreate($name);
    }

    public function delete($id): void
    {
        Role::find($id)?->delete();
    }
}
```

Note: no `TenantScope` here — Spatie's `Role` model is a package model, not one of this app's own Eloquent models, and roles aren't tenant-scoped data in this design (see spec: Teams feature intentionally off).

- [ ] **Step 5: Create the service**

Create `app/Services/Admin/RoleService.php`:
```php
<?php

namespace App\Services\Admin;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(private RoleRepositoryInterface $repository)
    {
    }

    public function listRolesWithPermissions(): Collection
    {
        return $this->repository->allWithPermissions();
    }

    public function allPermissionNames(): array
    {
        return RolesAndPermissionsSeeder::PERMISSIONS;
    }

    public function createRole(string $name): Role
    {
        return $this->repository->create($name);
    }

    public function deleteRole($id): void
    {
        $this->repository->delete($id);
    }

    public function togglePermission($roleId, string $permissionName): bool
    {
        $role = $this->repository->find($roleId);
        Permission::findOrCreate($permissionName);

        if ($role->hasPermissionTo($permissionName)) {
            $role->revokePermissionTo($permissionName);
            return false;
        }

        $role->givePermissionTo($permissionName);
        return true;
    }
}
```

- [ ] **Step 6: Bind the interface**

Modify `app/Providers/RepositoryServiceProvider.php` — add the import and binding alongside the existing ones:
```php
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Eloquent\EloquentRoleRepository;
```
and inside `register()`:
```php
$this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
```

- [ ] **Step 7: Create the controller**

Create `app/Http/Controllers/Admin/RoleController.php`:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private RoleService $service)
    {
    }

    public function index()
    {
        return view('admin.roles', [
            'roles' => $this->service->listRolesWithPermissions(),
            'permissions' => $this->service->allPermissionNames(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $this->service->createRole($request->input('name'));

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function togglePermission(Request $request, $roleId)
    {
        $granted = $this->service->togglePermission($roleId, $request->input('permission'));

        return response()->json(['granted' => $granted]);
    }

    public function destroy($id)
    {
        $this->service->deleteRole($id);

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }
}
```

- [ ] **Step 8: Add the routes**

Modify `routes/web.php` — inside the existing main `Route::middleware('auth')->group(function () { ... })` block (the one starting at line 37 with `updateProfile`/`updatePassword`), add:
```php
    Route::middleware('permission:admin.manage-roles')->group(function () {
        Route::get('admin/roles', [App\Http\Controllers\Admin\RoleController::class, 'index'])->name('admin.roles.index');
        Route::post('admin/roles', [App\Http\Controllers\Admin\RoleController::class, 'store'])->name('admin.roles.store');
        Route::post('admin/roles/{roleId}/toggle-permission', [App\Http\Controllers\Admin\RoleController::class, 'togglePermission'])->name('admin.roles.togglePermission');
        Route::delete('admin/roles/{id}', [App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('admin.roles.destroy');
    });
```
This requires the `permission` middleware alias to exist — add it now to `app/Http/Kernel.php`'s `$routeMiddleware` array (needed by this task, not deferred to Task 6, since these routes use it immediately):
```php
'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
```

- [ ] **Step 9: Create the matrix view**

Create `resources/views/admin/roles.blade.php`:
```blade
@extends('layouts.master')
@section('title')
Roles & Permissions
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Admin
@endslot
@slot('title')
Roles & Permissions
@endslot
@endcomponent

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Create Role</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <input type="text" class="form-control" name="name" placeholder="Role name" required>
                    </div>
                    <button type="submit" class="btn btn-success">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Permission Matrix</h5></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Permission</th>
                            @foreach($roles as $role)
                                <th class="text-center">
                                    {{ $role->name }}
                                    @if($role->name !== 'Owner')
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0 ms-1">&times;</button>
                                        </form>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $permission)
                        <tr>
                            <td>{{ $permission }}</td>
                            @foreach($roles as $role)
                                <td class="text-center">
                                    <input type="checkbox"
                                        class="form-check-input permission-toggle"
                                        data-role-id="{{ $role->id }}"
                                        data-permission="{{ $permission }}"
                                        {{ $role->permissions->pluck('name')->contains($permission) ? 'checked' : '' }}>
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.permission-toggle').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            var roleId = this.dataset.roleId;
            var permission = this.dataset.permission;
            var checkboxEl = this;

            fetch('/admin/roles/' + roleId + '/toggle-permission', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ permission: permission }),
            })
            .then(function (res) { return res.json(); })
            .then(function (data) { checkboxEl.checked = data.granted; })
            .catch(function () { checkboxEl.checked = !checkboxEl.checked; });
        });
    });
});
</script>
@endsection
```

- [ ] **Step 10: Run test to verify it passes**

Run: `php artisan test --filter=Admin\\\\RoleTest`
Expected: PASS (all 5 tests).

- [ ] **Step 11: Run the full suite**

Run: `php artisan test`
Expected: all previously-passing tests still pass (this task adds new middleware only to the brand-new `admin/roles*` routes, no existing route touched yet).

- [ ] **Step 12: Commit**

```bash
git add app/Repositories/Contracts/RoleRepositoryInterface.php app/Repositories/Eloquent/EloquentRoleRepository.php app/Services/Admin/RoleService.php app/Http/Controllers/Admin/RoleController.php resources/views/admin/roles.blade.php app/Providers/RepositoryServiceProvider.php app/Http/Kernel.php routes/web.php tests/Feature/Admin/RoleTest.php
git commit -m "Add role management + permission matrix UI

New Admin\RoleController following the existing Controller -> Service
-> Repository pattern. Matrix view lets the Owner create roles and
toggle permissions per-checkbox via AJAX (matching the app's existing
quantityupdate-style toggle pattern). Adds the 'permission' route
middleware alias, used by this and every later task."
```

---

### Task 5: User repository extension/service/controller + management UI

**Files:**
- Modify: `app/Repositories/Contracts/UserRepositoryInterface.php`
- Modify: `app/Repositories/Eloquent/EloquentUserRepository.php`
- Create: `app/Services/Admin/UserService.php`
- Create: `app/Http/Controllers/Admin/UserController.php`
- Create: `resources/views/admin/users.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/UserTest.php`

**Interfaces:**
- Consumes: `UserRepositoryInterface::find($id)` (existing, Home/Auth refactor), `RoleService::listRolesWithPermissions()` pattern reference (Task 4) for the role dropdown.
- Produces: `UserService::listAllWithRoles(): Collection`, `UserService::createUser(array $data): User`, `UserService::updateRoleAndStatus($id, string $roleName, bool $isActive): User`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/UserTest.php`:
```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_users_page_renders_with_existing_users(): void
    {
        $owner = User::factory()->create(['name' => 'Existing Owner']);

        $response = $this->actingAs($owner)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Existing Owner');
    }

    public function test_owner_can_create_a_worker_with_a_temporary_password(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->post(route('admin.users.store'), [
            'name' => 'New Worker',
            'email' => 'worker@example.com',
            'password' => 'TempPass123',
            'role' => 'Worker',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'worker@example.com', 'is_active' => 1]);

        $newUser = User::where('email', 'worker@example.com')->first();
        $this->assertTrue($newUser->hasRole('Worker'));
        $this->assertTrue(Hash::check('TempPass123', $newUser->password));
    }

    public function test_owner_can_change_a_users_role_and_deactivate_them(): void
    {
        $owner = User::factory()->create();
        $target = User::factory()->create();
        $target->syncRoles(['Worker']);

        $response = $this->actingAs($owner)->put(route('admin.users.update', $target->id), [
            'role' => 'Worker',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_worker_cannot_reach_user_management(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $response = $this->actingAs($worker)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=Admin\\\\UserTest`
Expected: FAIL — route `admin.users.index` not defined.

- [ ] **Step 3: Extend the repository interface**

Modify `app/Repositories/Contracts/UserRepositoryInterface.php`:
```php
<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function find($id): ?User;

    public function allWithRoles(): Collection;

    public function create(array $data): User;

    public function save(User $user): void;
}
```

- [ ] **Step 4: Extend the Eloquent implementation**

Modify `app/Repositories/Eloquent/EloquentUserRepository.php`:
```php
<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Support\Tenancy\TenantScope;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(private TenantScope $tenantScope)
    {
    }

    public function find($id): ?User
    {
        return $this->tenantScope->apply(User::query())->find($id);
    }

    public function allWithRoles(): Collection
    {
        return $this->tenantScope->apply(User::with('roles'))->orderBy('name')->get();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function save(User $user): void
    {
        $user->save();
    }
}
```

- [ ] **Step 5: Create the service**

Create `app/Services/Admin/UserService.php`:
```php
<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(private UserRepositoryInterface $repository)
    {
    }

    public function listAllWithRoles(): Collection
    {
        return $this->repository->allWithRoles();
    }

    public function createUser(array $data): User
    {
        $user = $this->repository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $user->syncRoles([$data['role']]);

        return $user;
    }

    public function updateRoleAndStatus($id, string $roleName, bool $isActive): User
    {
        $user = $this->repository->find($id);
        $user->syncRoles([$roleName]);
        $user->is_active = $isActive;
        $this->repository->save($user);

        return $user;
    }
}
```

- [ ] **Step 6: Create the controller**

Create `app/Http/Controllers/Admin/UserController.php`:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RoleService;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $service, private RoleService $roleService)
    {
    }

    public function index()
    {
        return view('admin.users', [
            'users' => $this->service->listAllWithRoles(),
            'roles' => $this->roleService->listRolesWithPermissions(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
        ]);

        $this->service->createUser($request->only(['name', 'email', 'password', 'role']));

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
            'is_active' => 'required|boolean',
        ]);

        $this->service->updateRoleAndStatus($id, $request->input('role'), (bool) $request->input('is_active'));

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }
}
```

- [ ] **Step 7: Add the routes**

Modify `routes/web.php` — inside the same `permission:admin.manage-roles` block added in Task 4, add a sibling block (separate permission, `admin.manage-users`):
```php
    Route::middleware('permission:admin.manage-users')->group(function () {
        Route::get('admin/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
        Route::post('admin/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
        Route::put('admin/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    });
```

- [ ] **Step 8: Create the view**

Create `resources/views/admin/users.blade.php`:
```blade
@extends('layouts.master')
@section('title')
Users
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Admin
@endslot
@slot('title')
Users
@endslot
@endcomponent

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Create User</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Temporary Password</label>
                        <input type="text" class="form-control" name="password" minlength="8" required>
                        <div class="form-text">Share this with the user directly. They can change it after logging in via their profile page.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Create User</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">All Users</h5></div>
            <div class="card-body table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->roles->pluck('name')->first() ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
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
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 9: Run test to verify it passes**

Run: `php artisan test --filter=Admin\\\\UserTest`
Expected: PASS (all 4 tests).

- [ ] **Step 10: Run the full suite**

Run: `php artisan test`
Expected: all previously-passing tests still pass.

- [ ] **Step 11: Commit**

```bash
git add app/Repositories/Contracts/UserRepositoryInterface.php app/Repositories/Eloquent/EloquentUserRepository.php app/Services/Admin/UserService.php app/Http/Controllers/Admin/UserController.php resources/views/admin/users.blade.php routes/web.php tests/Feature/Admin/UserTest.php
git commit -m "Add user management screen (create/edit role/activate-deactivate)

Extends the existing UserRepositoryInterface from the Home/Auth
refactor rather than creating a duplicate. Owner sets a temporary
password directly at creation time (no email delivery - mail isn't
configured in this environment); the existing change-password flow
already lets the new user change it after logging in."
```

---

### Task 6: Apply permission middleware to every existing route + sidebar `@can` wraps

**Files:**
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/sidebar.blade.php`
- Test: `tests/Feature/PermissionEnforcementTest.php`

**Interfaces:**
- Consumes: the permission list from Task 2, `permission` middleware alias from Task 4.
- Produces: nothing new consumed by later tasks — this is the enforcement layer itself.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/PermissionEnforcementTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new RolesAndPermissionsSeeder())->run();
    }

    public function test_owner_can_reach_every_gated_route(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->get(route('product'))->assertOk();
        $this->actingAs($owner)->get(route('invoice'))->assertOk();
        $this->actingAs($owner)->get(route('invoice.inventrylist'))->assertOk();
        $this->actingAs($owner)->get(route('invoice.vender'))->assertOk();
        $this->actingAs($owner)->get(route('invoice.histry'))->assertOk();
        $this->actingAs($owner)->get(route('listqutation'))->assertOk();
    }

    public function test_worker_with_no_permissions_is_blocked_from_every_gated_route(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);

        $this->actingAs($worker)->get(route('product'))->assertStatus(403);
        $this->actingAs($worker)->get(route('invoice'))->assertStatus(403);
        $this->actingAs($worker)->get(route('invoice.inventrylist'))->assertStatus(403);
        $this->actingAs($worker)->get(route('invoice.vender'))->assertStatus(403);
        $this->actingAs($worker)->get(route('invoice.histry'))->assertStatus(403);
        $this->actingAs($worker)->get(route('listqutation'))->assertStatus(403);
    }

    public function test_worker_granted_a_single_permission_can_reach_only_that_route(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $worker->givePermissionTo('invoices.view');

        $this->actingAs($worker)->get(route('invoice'))->assertOk();
        $this->actingAs($worker)->get(route('product'))->assertStatus(403);
    }

    public function test_dashboard_is_reachable_by_worker_with_dashboard_permission(): void
    {
        $worker = User::factory()->create();
        $worker->syncRoles(['Worker']);
        $worker->givePermissionTo('dashboard.view');

        $this->actingAs($worker)->get(route('root'))->assertOk();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PermissionEnforcementTest`
Expected: the "Worker is blocked" tests FAIL (currently everything is 200 for any authenticated user, no middleware applied yet).

- [ ] **Step 3: Apply middleware to existing routes**

Modify `routes/web.php`. This task touches route registrations only, not controllers/views. Apply `permission:` middleware per this exact mapping (read the current file first — three route-registration blocks currently exist: the early "must be before catch-all" group, the main `auth` group, and the root `/` route from Task 3's work):

For the root route (`Route::middleware('auth')->get('/', ...)`), change to:
```php
Route::middleware(['auth', 'permission:dashboard.view'])->get('/', [App\Http\Controllers\Home\HomeController::class, 'root'])->name('root');
```

For the early "before catch-all" group, wrap each route individually with its own permission (since they're in one `auth` group but need different permissions each — nest the `permission:` middleware per-route rather than per-group):
```php
Route::middleware('auth')->group(function () {
    Route::get('apps-invoices-create' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'create'])->name('invoice.create')->middleware('permission:invoices.create');
    Route::get('paymenthistry', [App\Http\Controllers\Invoice\InvoiceController::class, 'paymenthistry'])->name('invoice.histry')->middleware('permission:payment-history.view');
    Route::get('apps-invoices-list' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'index'])->name('invoice')->middleware('permission:invoices.view');
    Route::get('vender', [App\Http\Controllers\Invoice\InvoiceController::class, 'vender'])->name('invoice.vender')->middleware('permission:vendors.view');
    Route::get('inventrylist', [App\Http\Controllers\Inventory\InventryController::class, 'inventry'])->name('invoice.inventrylist')->middleware('permission:inventory.view');
});
```

For the main `auth` group (the large block starting `//Update User Details & Auth-protected routes`), append `->middleware('permission:...')` to each route per this mapping (leave `updateProfile`/`updatePassword`/`co2quation`/`Co2quationstore` and the Task 4/5 admin routes exactly as they are — profile/password routes intentionally have no permission gate beyond `auth`, since every authenticated user manages their own account regardless of role; `co2quation`/`Co2quationstore` are quotations too, gate with `quotations.view`/`quotations.create` respectively):

```php
    Route::get('/co2quation/{id}' ,[App\Http\Controllers\Quotation\QutationController::class,'Co2quation'])->name('co2quation')->middleware('permission:quotations.view');
    Route::post('/co2quationstore' ,[App\Http\Controllers\Quotation\QutationController::class,'Co2quationstore'])->name('Co2quationstore')->middleware('permission:quotations.create');

    Route::get('apps-invoices-list/data' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'listData'])->name('invoice.data')->middleware('permission:invoices.view');
    Route::get('admin/invoice/getproductvalue' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'getproduct'])->name('invoice.product')->middleware('permission:invoices.create');
    Route::get('admin/invoice/getproduct1' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'getproductvalue1'])->name('invoice.product1')->middleware('permission:invoices.create');
    Route::post('update-payment', [App\Http\Controllers\Invoice\InvoiceController::class, 'updatePayment'])->middleware('permission:invoices.record-payment');
    Route::post('invoicestore' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'store'])->name('invoice.store')->middleware('permission:invoices.create');
    Route::get('invoiceddetails/{id}' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'details'])->name('invoice.details')->middleware('permission:invoices.view-details');

    Route::post('inventrystore', [App\Http\Controllers\Inventory\InventryController::class, 'inventrystore'])->name('inventrystore')->middleware('permission:inventory.create');
    Route::post('quantityupdate', [App\Http\Controllers\Inventory\InventryController::class, 'quantityupdate'])->name('quantityupdate')->middleware('permission:inventory.update');

    Route::get('product' ,[App\Http\Controllers\Product\ProductController::class, 'index'])->name('product')->middleware('permission:products.view');
    Route::post('productstore' ,[App\Http\Controllers\Product\ProductController::class, 'productstore'])->name('productstore')->middleware('permission:products.create');
    Route::get('productdelete/{id}' ,[App\Http\Controllers\Product\ProductController::class, 'delete'])->name('product.delete')->middleware('permission:products.delete');

    Route::get('standerconfig/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'standerconfig'])->name('standerconfig')->middleware('permission:products.manage-config');
    Route::get('TechnicalParameters/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'technicalparameters'])->name('TechnicalParameters')->middleware('permission:products.manage-config');
    Route::get('standerconfiglist/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'standerconfiglist'])->name('standerconfiglist')->middleware('permission:products.manage-config');

    Route::get('softerwere/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softerwere'])->name('softerwere')->middleware('permission:products.manage-config');
    Route::post('softerwere/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softerwerestore'])->name('softerwerestore')->middleware('permission:products.manage-config');
    Route::post('cutting/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingstore'])->name('cuttingstore')->middleware('permission:products.manage-config');
    Route::post('focusing/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'focusingstore'])->name('focusingstore')->middleware('permission:products.manage-config');
    Route::post('power/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'powerstore'])->name('powerstore')->middleware('permission:products.manage-config');
    Route::get('softerwere/show' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softerwereshow'])->name('softerwere.show')->middleware('permission:products.manage-config');

    Route::post('cuttingway/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingwaystore'])->name('cuttingwaystore')->middleware('permission:products.manage-config');
    Route::post('cncthinkness/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cncthinknessstore'])->name('cncthinknessstore')->middleware('permission:products.manage-config');
    Route::get('cuttingway/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingway'])->name('cuttingway')->middleware('permission:products.manage-config');

    Route::post('motor/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'motorstore'])->name('motorstore')->middleware('permission:products.manage-config');
    Route::post('gear/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'gearstore'])->name('gearstore')->middleware('permission:products.manage-config');
    Route::post('rack/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'rackstore'])->name('rackstore')->middleware('permission:products.manage-config');
    Route::post('Software/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softwarestore'])->name('softwarestore')->middleware('permission:products.manage-config');

    Route::get('fiberqutation/{id}' ,[App\Http\Controllers\Quotation\QutationController::class, 'generatequtation'])->name('generatequtation')->middleware('permission:quotations.view');
    Route::post('fiberqutation/store' ,[App\Http\Controllers\Quotation\QutationController::class, 'generatequtationstore'])->name('generatequtationstore')->middleware('permission:quotations.create');
    Route::get('admin/listqutation' ,[App\Http\Controllers\Quotation\QutationController::class, 'index'])->name('listqutation')->middleware('permission:quotations.view');
    Route::get('/printquation/{id}', [App\Http\Controllers\Quotation\QutationController::class, 'print'])->name('quation.pdf')->middleware('permission:quotations.download-pdf');
```

- [ ] **Step 4: Wrap sidebar links in `@can`**

Modify `resources/views/layouts/sidebar.blade.php` — wrap each existing `<li class="nav-item">` block in the matching `@can(...)`/`@endcan`:
```blade
                @can('products.view')
                <li class="nav-item">
                    <a href="{{route('product')}}" class="nav-link"><i class="ri-dashboard-2-line"></i><span>@lang('Product')</span></a>
                </li>
                @endcan
                @can('invoices.view')
                <li class="nav-item">
                    <a href="{{route('invoice')}}" class="nav-link"><i class="ri-dashboard-2-line"></i><span>@lang('Invoice')</span></a>
                </li>
                @endcan
                @can('payment-history.view')
                <li class="nav-item">
                    <a href="{{route('invoice.histry')}}" class="nav-link"><i class="ri-dashboard-2-line"></i><span>@lang('Payment history')</span></a>
                </li>
                @endcan
                @can('inventory.view')
                <li class="nav-item">
                    <a href="{{route('invoice.inventrylist')}}" class="nav-link"><i class="ri-dashboard-2-line"></i><span>@lang('Invtery managemnet')</span></a>
                </li>
                @endcan
                @can('products.manage-config')
                <li class="nav-item">
                    <a href="#sidebarEcommerce" class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarEcommerce">@lang('Fiber Laser Cutting')
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarEcommerce">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{route('standerconfig' ,1)}}" class="nav-link">@lang('translation.Stander Config')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('TechnicalParameters' ,1)}}"class="nav-link">@lang('translation.Technical Paramater')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('standerconfiglist',1)}}" class="nav-link">@lang('translation.Standerd Config')</a>
                            </li>
                            @can('quotations.view')
                            <li class="nav-item">
                                <a href="{{route('generatequtation',1)}}" class="nav-link">@lang('translation.Quation')</a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcan
                @can('quotations.view')
                <li class="nav-item">
                    <a href="{{route('co2quation',1)}}" class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarEcommerce">@lang('Co2 Laser Cutting')
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('listqutation')}}" class="nav-link"><i class="ri-dashboard-2-line"></i><span>@lang('Quation')</span></a>
                </li>
                @endcan
                @canany(['admin.manage-roles', 'admin.manage-users'])
                <li class="menu-title"><span>Admin</span></li>
                @can('admin.manage-roles')
                <li class="nav-item">
                    <a href="{{ route('admin.roles.index') }}" class="nav-link"><i class="ri-shield-user-line"></i><span>Roles & Permissions</span></a>
                </li>
                @endcan
                @can('admin.manage-users')
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link"><i class="ri-user-settings-line"></i><span>Users</span></a>
                </li>
                @endcan
                @endcanany
```
(The existing malformed stray `</li>` at the original line 47 and the incomplete `standerconfiglist`/`generatequtation` `<li>` nesting from the pre-existing file should be preserved/fixed only as needed to keep this block's HTML valid — read the actual current file content before editing, since line numbers shift once Task 4/5 changes land; match against the text shown above, not fixed line numbers.)

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=PermissionEnforcementTest`
Expected: PASS (all 4 tests).

- [ ] **Step 6: Run the full suite — expect and fix breakage**

Run: `php artisan test`
Expected: some of the 24 pre-existing tests (Inventory/Quotation/Home/Invoice/QuotationTest etc.) may now fail if they call `actingAs($user)` with a user who doesn't have the right permission for that specific route. Since Task 2's `UserFactory` already assigns Owner (all permissions) by default, this should NOT happen for a bare `User::factory()->create()` — but re-run and check output carefully. If any test does fail, it means that test explicitly overrode the user's roles/permissions earlier in the file (e.g. via `syncRoles`) for an unrelated reason — fix by ensuring the acting user has the needed permission, not by loosening the route's middleware.

- [ ] **Step 7: Manual smoke test against the real live DB**

Start the dev server and confirm existing (Owner) access is completely unchanged:
```bash
php -S 127.0.0.1:8000 router.php &
```
Log in as `admin@gmail.com` (per `docs/QA-Dev-Work-Log.xlsx`) — but first, this account needs the Owner role assigned in the live DB, which doesn't happen until Task 8's one-time data seeder runs. **Skip the live-DB portion of this step until after Task 8**; for now, verify via the automated test suite only (Step 6) plus a check that routes/web.php has no syntax errors:
```bash
php -l routes/web.php
```
Expected: `No syntax errors detected`.

- [ ] **Step 8: Commit**

```bash
git add routes/web.php resources/views/layouts/sidebar.blade.php tests/Feature/PermissionEnforcementTest.php
git commit -m "Enforce permissions on every existing route + gate sidebar links

Every route now requires the matching permission from the 24-item
list (see docs/DEVELOPMENT-STANDARDS.md-adjacent spec). Sidebar links
wrapped in @can so a Worker only sees what they can access. Profile/
password routes deliberately ungated beyond auth - every user manages
their own account regardless of role. Full smoke test against the
live DB deferred to the final task, since the real admin accounts
don't have the Owner role assigned until then."
```

---

### Task 7: Remove signup

**Files:**
- Modify: `routes/web.php`
- Modify: `resources/views/auth/login.blade.php`
- Delete: `app/Http/Controllers/Auth/RegisterController.php`
- Delete: `resources/views/auth/register.blade.php`
- Test: `tests/Feature/Auth/RegistrationRemovedTest.php`

**Interfaces:**
- None — this task is self-contained.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Auth/RegistrationRemovedTest.php`:
```php
<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRemovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_route_no_longer_exists(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_login_page_has_no_signup_link(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertDontSee('Signup');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=RegistrationRemovedTest`
Expected: FAIL — `/register` currently returns 200, and "Signup" is currently visible on the login page.

- [ ] **Step 3: Disable the register routes**

Modify `routes/web.php` — change:
```php
Auth::routes();
```
to:
```php
Auth::routes(['register' => false]);
```

- [ ] **Step 4: Remove the signup link from the login view**

Modify `resources/views/auth/login.blade.php` — delete line 98 (`<p class="mb-0">Don't have an account ? <a href="{{ route('register') }}" ...`) entirely.

- [ ] **Step 5: Delete the now-orphaned register controller and view**

```bash
rm "app/Http/Controllers/Auth/RegisterController.php"
rm "resources/views/auth/register.blade.php"
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=RegistrationRemovedTest`
Expected: PASS (both tests).

- [ ] **Step 7: Run the full suite**

Run: `php artisan test`
Expected: all tests still pass — nothing else references `route('register')` or the deleted files (confirm with `grep -rn "RegisterController\|route('register')" app resources routes` before this step, to be sure nothing else breaks).

- [ ] **Step 8: Commit**

```bash
git add routes/web.php resources/views/auth/login.blade.php
git rm app/Http/Controllers/Auth/RegisterController.php resources/views/auth/register.blade.php
git commit -m "Remove public self-registration

All accounts are now admin-created via the new Users screen (Task 5)
with an admin-set temporary password. Public signup no longer fits
this app's access model. Deleted the now-fully-orphaned
RegisterController and its view rather than leaving dead code."
```

---

### Task 8: Live-DB rollout + full verification + work-log update

**Files:**
- Modify: `docs/QA-Dev-Work-Log.xlsx` (via Python/openpyxl, matching the pattern used earlier this session)
- No new application code — this task is verification and one-time data migration only.

**Interfaces:**
- None.

- [ ] **Step 1: Run the full automated suite one more time**

Run: `php artisan test`
Expected: 100% pass (this feature's new tests plus all 24 pre-existing ones).

- [ ] **Step 2: Run migrations against the live MySQL database**

```bash
php artisan migrate
```
Expected: the two new migrations (Spatie's permission tables, `is_active` column) apply cleanly. This is a normal migration run — unlike the earlier session's 18-table backfill, these are genuinely new tables/columns that never existed before, so no special "register as already-applied" bookkeeping is needed here.

- [ ] **Step 3: Seed roles/permissions on the live database**

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```
Expected: creates the 24 permissions and Owner/Worker roles on the live DB (idempotent — safe if run again later).

- [ ] **Step 4: One-time: assign Owner role to the 3 existing real accounts**

```bash
php artisan tinker --execute="
App\Models\User::whereIn('email', ['admin@gmail.com', 'patelmeet23599@gmail.com', 'patelmeet23599@oksbi'])->get()->each(function (\$u) {
    \$u->assignRole('Owner');
    echo \$u->email . ' => Owner assigned' . PHP_EOL;
});
"
```
Expected: 3 lines of output confirming assignment. Verify:
```bash
php artisan tinker --execute="App\Models\User::with('roles')->get(['id','email'])->each(function(\$u){ echo \$u->email . ': ' . \$u->roles->pluck('name')->implode(',') . PHP_EOL; });"
```
Expected: all 3 show `Owner`.

- [ ] **Step 5: Manual smoke test against the live DB**

```bash
php -S 127.0.0.1:8000 router.php &
```
Log in as `admin@gmail.com` (password from `docs/QA-Dev-Work-Log.xlsx` / this session's memory), confirm:
- Dashboard, Product, Invoice, Inventory, Vendor, Payment History, Quotation pages all still load (200) exactly as before.
- New "Admin" sidebar section appears with "Roles & Permissions" and "Users" links.
- Create a test Worker user via the Users page with a temporary password.
- Log out, log in as the test Worker — confirm the sidebar shows only Dashboard, confirm hitting `/product` directly returns 403 (not a crash).
- As the Worker, use the existing change-password flow to change their own password — confirm it works.
- Log back in as Owner, deactivate the test Worker account.
- Attempt to log in as the deactivated Worker — confirm login is rejected with the "deactivated" message.
- Delete the test Worker account (or leave deactivated — either is fine, document which in the work log) via tinker cleanup:
```bash
php artisan tinker --execute="App\Models\User::where('email', '<test-worker-email>')->delete();"
```

Stop the dev server:
```bash
pkill -f "php -S 127.0.0.1:8000"
```

- [ ] **Step 6: Update the work-log spreadsheet**

Follow the same pattern established earlier this session (Python/openpyxl, preserving existing formatting — see the git history around commit `16f3268` for the exact technique). Add to `docs/QA-Dev-Work-Log.xlsx`:
- **Summary sheet:** a new phase entry for the role matrix feature (packages installed, migrations added, domains added, routes gated).
- **Manual Commands & Server sheet:** the one-time live-DB role-assignment command from Step 4 (this is exactly the kind of "manual step outside git" this sheet exists to capture), and a note that `php artisan db:seed --class=RolesAndPermissionsSeeder` must run on any new environment (fresh or existing) after migrating, since permissions/roles aren't created by the migrations themselves.
- Commit the updated spreadsheet.

- [ ] **Step 7: Final commit and push**

```bash
cd "/Users/meetpatel/_public_html (1)"
git status
git add docs/QA-Dev-Work-Log.xlsx
git commit -m "Update work log with role-matrix rollout steps and live-DB migration"
git log --oneline -10
git push origin meet-update
```
Expected: all commits from this plan (Tasks 1-8) pushed to `origin/meet-update`.

---

## Self-review notes (from the plan author)

- **Spec coverage:** every section of `docs/superpowers/specs/2026-07-17-role-matrix-design.md` maps to a task — Architecture/Data model → Task 1, Permission list/Seed roles → Task 2, User creation/temp passwords → Task 5, UI → Tasks 4/5, is_active login gate → Task 3, signup removal → Task 7, Testing & rollout → Task 8. Out-of-scope items (job tracking, Teams, forced password change, email delivery, multi-role) are not implemented anywhere in this plan, matching the spec.
- **Known gotcha addressed explicitly:** Spatie's permission cache vs. `RefreshDatabase` — handled once, globally, in Task 1's `TestCase::setUp()`, so no individual test file needs to remember it.
- **Route-permission mapping is exhaustive** for every route that existed before this plan (cross-checked against the full `routes/web.php` dump used in Task 6) — nothing was left ungated by omission.
