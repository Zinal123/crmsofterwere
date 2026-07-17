# Dynamic Role Matrix (RBAC) — Design Spec

**Date:** 2026-07-17
**Status:** Approved, ready for implementation planning
**Sub-project 1 of 2.** The user's original request combined a role matrix and a job-tracking
feature (field workers photograph completed site work). Per the standard decomposition
approach, this spec covers **only the role matrix** — the foundation the job-tracking feature
will build on. Job tracking gets its own design pass once this ships, and its permissions
(`jobs.create`, `jobs.upload-photo`, `jobs.mark-complete`, etc.) get defined then, not here.

## Context

The app currently has zero access control: every logged-in user has identical, unrestricted
access to everything. Only 3 user accounts exist, all created directly in the database — there
is no UI to create a user at all. The business now needs to onboard field workers with
deliberately restricted access (they'll only interact with the job-tracking feature once it
exists), and wants that access configurable without a code change every time a new role or
permission need comes up — i.e. a **dynamic** role matrix: roles and permissions live in the
database, and an admin screen lets the Owner create roles and check/uncheck permissions in a
grid.

Multi-tenancy (client companies logging in) was discussed and explicitly deferred — "no plan to
sell this software yet." This design does not build toward that, but doesn't foreclose it either
(see Architecture, Teams).

## Architecture

- **Package: `spatie/laravel-permission`.** Industry-standard, actively maintained Laravel
  package purpose-built for exactly this (DB-backed roles/permissions, `$user->can(...)`,
  `@can(...)` Blade directives, `permission:` route middleware). Building this by hand would
  mean re-implementing a well-tested wheel for no benefit.
- **Teams feature: off.** Spatie supports scoping roles per-tenant ("teams"), which maps
  directly to the deferred multi-tenancy work, but turning it on now adds overhead to every
  permission check for a feature not being built yet. It has a documented upgrade path — enable
  it when multi-tenancy actually starts. This is the standards doc's scope-discipline rule in
  practice: build what's asked, not what might be needed later.
- **Follows the existing Controller → Service → Repository pattern** (see
  `docs/DEVELOPMENT-STANDARDS.md` §1). New `Admin` domain:
  - `App\Http\Controllers\Admin\RoleController` (roles + permission matrix)
  - `App\Http\Controllers\Admin\UserController` (user CRUD, role assignment, activate/deactivate)
  - Backing services (`RoleService`, `UserService`) and repositories
    (`RoleRepositoryInterface`/`EloquentRoleRepository`,
    `UserRepositoryInterface`/`EloquentUserRepository` — note `UserRepositoryInterface` already
    exists from the Home/Auth refactor, extend it rather than duplicating).
- **Route protection is the real enforcement, not just hidden menu items.** Every existing route
  gets a `permission:` middleware matching the table below. A UI that only *hides* a link a user
  could still hit directly isn't real access control.

## Data model

- Spatie's own migrations: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`,
  `role_has_permissions`. Standard package install, no customization needed.
- `users` table: **no `role_id` column.** Spatie links roles via its own pivot table. "One role
  per user" is a *product rule*, enforced in the assignment UI/service layer (reject assigning a
  second role), not a schema constraint — keeps the door open if that rule ever changes, without
  a migration.
- `users.is_active` (boolean, default `true`) — **new column, new migration.** Lets the Owner
  deactivate a worker who leaves without deleting their historical records (job records, once
  that feature exists, should survive the user who created them).
- **Signup removed:** `Auth::routes()` in `routes/web.php` changes to
  `Auth::routes(['register' => false]);`. The "Register" link (if present anywhere in
  `layouts/auth-*` views) is removed. All accounts are now admin-created only, matching the new
  user-management screen.

## Permission list

Per-action, grouped by existing module — only for modules that exist today:

| Module | Permissions |
|---|---|
| Dashboard | `dashboard.view` |
| Products | `products.view`, `products.create`, `products.delete`, `products.manage-config` (the ~10 config sub-forms — software/laser/motor/gear/etc. — as one permission; they're one cohesive "configure a product" workflow) |
| Inventory | `inventory.view`, `inventory.create`, `inventory.update` |
| Invoices | `invoices.view`, `invoices.create`, `invoices.view-details`, `invoices.record-payment` |
| Vendors/Customers | `vendors.view` |
| Payment History | `payment-history.view` |
| Quotations | `quotations.view`, `quotations.create`, `quotations.download-pdf` |
| Admin: Roles | `admin.manage-roles` |
| Admin: Users | `admin.manage-users` |

19 permissions total. Job-tracking permissions are deliberately excluded (see header note).

## Seed roles

- **Owner** — gets every permission above, including `admin.manage-roles` and
  `admin.manage-users`. Assigned automatically to the 3 existing real user accounts via a
  one-time data-migration seeder (see Rollout).
- **Worker** — created with no permissions assigned initially (there's nothing for a worker to
  do yet; job-tracking permissions get attached to this role when that feature ships). Exists as
  a seed role so the Owner doesn't have to create it by hand before onboarding the first worker.
- Additional roles (e.g. a future "Manager") are created by the Owner through the matrix UI —
  no code change required. This is the point of "dynamic."

## User creation & temporary passwords

- New "Create User" form (Admin → Users): Name, Email, Role (dropdown, single-select), Password
  (the Owner types a temporary password directly — no auto-generation-and-email, since this
  environment's mail config points at `mailhog`, a local dev catcher, not a real SMTP provider;
  building real email delivery wasn't asked for and is out of scope here), Active toggle
  (defaults on).
- The Owner shares the temporary password with the worker directly (same manual-communication
  pattern already used for this app's own admin password resets earlier in this project).
- **No forced "must change password on first login" flow** — the existing change-password
  feature (`HomeController@updatePassword`, already refactored to
  `App\Http\Controllers\Home\HomeController` this session) remains available to every
  authenticated user regardless of role, so a worker can change their password whenever they
  want after logging in. Scoped this way deliberately — a forced-change interstitial is a
  reasonable future addition but wasn't requested and adds a new flow (detecting "must change,"
  redirecting, blocking other routes until changed) beyond what's needed right now.

## UI

- New sidebar section **"Admin"**, visible only via `@can('admin.manage-roles')` /
  `@can('admin.manage-users')` — consistent with every other permission-gated menu item (see
  below).
- **Roles & Permissions page:** list of roles (create / rename / delete a role — deleting a role
  in use requires reassigning affected users first, standard Spatie safety behavior), and a
  checkbox matrix: permissions as rows grouped by module (matching the table above), one column
  per role, check/uncheck to grant/revoke. Saves via AJAX per checkbox toggle, matching this
  app's existing pattern (e.g. inventory's `quantityupdate`) rather than a single giant form
  submit.
- **Users page:** table of users (name, email, role, active/inactive badge), "Create User" form
  as described above, edit existing user's role/active status. Deactivating a user does not
  delete them or their historical data — just blocks login (`is_active` check in the login flow).
- Every existing sidebar link (`resources/views/layouts/sidebar.blade.php`) gets wrapped in the
  matching `@can(...)` check from the permission table, so a Worker (once one exists) only sees
  what they have access to. Dashboard is the one page every authenticated user can reach
  regardless of role (`dashboard.view` granted to both seed roles by default) so nobody logs in
  to a blank sidebar with nowhere to go.

## Testing & rollout

- New feature tests (`tests/Feature/Admin/{Role,User}Test.php`): role CRUD, permission-matrix
  toggle, user CRUD, activate/deactivate, and **route-level enforcement** — a user without a
  given permission gets `403` on the matching route, a user with it gets through. Written against
  real route behavior first (per the standards doc's test-first rule), not just unit-testing the
  Spatie package itself.
- **Risk: breaking the 24 existing passing tests.** They all use `User::factory()->create()`
  with no role. Once routes carry `permission:` middleware, an unroled test user would 403 on
  everything. Fix: `UserFactory`'s default state assigns the **Owner** role (matches current
  reality — the app has exactly one real access level today). Tests that specifically need to
  verify a *restricted* role's behavior explicitly assign that role instead of relying on the
  factory default, overriding it the same way other factories already override defaults per-test.
- **One-time data migration:** a seeder assigns the Owner role to the 3 existing real user
  accounts (`admin@gmail.com` and the two `Meet Patel` accounts) so nobody loses access when this
  ships. Documented in `docs/QA-Dev-Work-Log.xlsx` → Manual Commands & Server, per the standards
  doc's schema/data-change rule — this touches the live database once, outside the normal
  migration-creates-empty-tables pattern.
- Manual smoke test after implementation: log in as Owner, confirm nothing changed (full access,
  unchanged); create a test Worker account with a temporary password, confirm they're blocked
  from admin/invoice/etc. routes (403, not a crash) and see only Dashboard in the sidebar; change
  that worker's password via the existing change-password flow to confirm it still works for a
  non-Owner role; deactivate the test account and confirm login is blocked; delete the test
  account after.

## Out of scope (explicitly, for this pass)

- Job-tracking feature and its permissions — sub-project 2, own design pass.
- Multi-tenancy / Spatie Teams — deferred per explicit user decision.
- Forced password change on first login.
- Email-based password delivery / auto-generated passwords.
- Multiple roles per user.
