# General Audit Log — Design

## Context

`docs/Oracle-Machine-Tech-Build-Order.pdf` flags a general audit log as a Phase 1 gap. Today only
Job Tracking has one (`job_audit_logs` + `JobAuditLogger`, a job-specific, manually-called
system). The user's explicit requirement: every section of the app needs a per-record audit
trail — field name, old value, new value, date/time, user, and action — and for events that
aren't a field edit (create, delete, activate/deactivate), the activity itself gets recorded
instead of a field diff. This must show up against every record in every section, not as one
single consolidated page.

This is a new, cross-cutting capability layered on top of every existing domain — not a new
domain of its own. Job Tracking's existing `job_audit_logs` is untouched; this is a second,
independent system for everything else.

## Confirmed Decisions

- **Storage:** one shared polymorphic `audit_logs` table, not one table per domain.
- **Capture:** automatic, via a reusable `Auditable` trait added to each model. No changes to
  existing controllers/services for the domains this covers directly.
- **Granularity:** one `audit_logs` row per changed field on an update. A save that changes 3
  fields produces 3 rows (same timestamp/actor).
- **Sensitive fields:** passwords are always excluded — a password change logs the fact (action
  `updated`, field `password`, no old/new values), never the hash. Everything else (bank account
  numbers, GST numbers, etc.) is logged normally — tracking unauthorized changes to payment
  details is a real business need here, not just noise.
- **Status fields:** boolean "is this record active" fields (`is_active` on Employee/User, etc.)
  get a special-cased `action` (`activated`/`deactivated`) instead of a generic field-diff row,
  matching the user's explicit "active deactive" wording.
- **Permissions:** one `.view-audit` permission per domain (not one blanket permission), following
  this app's existing per-domain permission convention (`employees.view` vs `attendance.view` vs
  `payroll.view` are already split the same way).
- **UI:** domains with an existing per-record detail page (Employee, Invoice) get an "Audit"
  section on that page, matching Job Tracking's existing pattern. Domains that are list-only today
  (Product, Inventory, Quotation) get a "History" icon per row opening a modal.

## Known Limitation (explicitly out of scope for automatic capture)

The trait only sees changes to a model's own columns via Eloquent's `created`/`updated`/`deleted`
events. It cannot see:
- **Many-to-many relationship changes** — role assignment (`syncRoles`/`assignRole`) writes to a
  pivot table, not a column on `User`. Covered instead by one manual log call added at the
  existing role-assignment action in `Admin\UserController`.
- **Spatie's `Role` model itself** — it's a vendor class (`Spatie\Permission\Models\Role`), not one
  of this app's own models, so the trait can't be attached to it without publishing a custom Role
  model (a bigger change, out of scope here). Role name/permission-set changes are covered by a
  manual log call in `Admin\RoleController`, the same file, same pattern as the role-assignment
  gap above.

Both of these are the only two places this feature uses manual calls; everything else is fully
automatic.

## Approach

### Schema

```
audit_logs
  id              bigint PK
  auditable_type  string   -- e.g. App\Models\Employee
  auditable_id    integer  -- polymorphic target
  action          enum: created, updated, deleted, activated, deactivated
  field_name      string, nullable   -- set only when action = updated
  old_value       text, nullable
  new_value       text, nullable
  user_id         integer, nullable  -- FK-type-safe per this app's users.id-is-signed-int
                                      -- convention: integer() + explicit foreign(), never
                                      -- foreignId(). Nullable so seeders/console commands
                                      -- that touch a model outside a request don't crash.
  created_at      timestamp
```

No `updated_at` — audit rows are append-only, never modified after creation.

### `App\Support\Auditing\Auditable` trait

Added to each model in scope (see Domains below). In `bootAuditable()`:
- `created` → one row, `action = created`, no field diff.
- `updated` → diff `getDirty()` against `getOriginal()`. Skip `updated_at` and a per-model
  `$auditExcept` array (every model that has a password-like field lists it here). For each
  remaining changed key: if it's in that model's `$auditStatusFields` (e.g. `['is_active']`), emit
  one row with `action = activated`/`deactivated` (based on the new value) instead of a field-diff
  row; otherwise emit one row with `action = updated`, `field_name`, `old_value`, `new_value`.
- `deleted` → one row, `action = deleted`. Matters mainly for Product (real hard deletes); most
  other domains use `is_active` instead of deleting.
- Actor is `auth()->user()?->id`.
- The actual `AuditLog::create()` call goes through `AuditLogRepositoryInterface::record()`
  (resolved via the container from inside the trait), keeping this app's
  Controller → Service → Repository convention intact even though the trigger is a model event,
  not a controller action — the trait never talks to Eloquent directly.

### Domains and permissions

| Domain | Models | Permission | UI |
|---|---|---|---|
| Product | `Product`, `Softerwere`, `Softerwere1`, `Lasercutting`, `Fource`, `Power`, `Motor`, `Gear`, `Rack`, `Cutting`, `Cnsthinks`, `Termandcondition` | `products.view-audit` | History modal from list row |
| Invoice | `Invoice`, `Customer`, `Invoiceproduct`, `Paidamount` | `invoices.view-audit` | Audit section on `invoice.details` |
| Quotation | `Quation` | `quotations.view-audit` | History modal from list row |
| Inventory | `Invetry` | `inventory.view-audit` | History modal from list row |
| Employee | `Employee`, `EmployeeDocument` | `employees.view-audit` | Audit section on `employees.edit` |
| Attendance | `Attendance` | `attendance.view-audit` | Audit section on `attendance.register` |
| Payroll | `SalaryPayment` | `payroll.view-audit` | Audit section on `payroll.show` |
| Admin | `User` (+ manual Role calls, see Known Limitation) | `admin.view-audit` | History modal from `admin/users` list row |
| Machines | `Machine` | `machines.view-audit` | History modal from list row — **proposed addition**, confirm during spec review: Machine CRUD exists in the Job Tracking module but isn't covered by `job_audit_logs` today |

`Bank` gets the trait applied for completeness (cheap, future-proof) but no UI/permission — there
is currently no create/update/delete route for Bank records anywhere in the app (it's populated
directly in the database and only read from for the invoice-creation bank dropdown), so there's
nothing for a History button to attach to yet.

### Shared UI

One reusable Blade component renders an audit trail table (columns: date/time, user, action,
field, old → new) given a collection of `AuditLog` rows — used identically by every domain's
"Audit" section or "History" modal, avoiding ~9 near-duplicate implementations. One shared
`AuditLogController@forRecord($type, $id)` endpoint serves the data, checking the correct
`.view-audit` permission for the given `$type` via a small type→permission map.

## Testing

Following this app's test-first-then-repo convention:
- Trait-level tests: updating a tracked field produces the correct row (field/old/new/action);
  updating multiple fields in one save produces one row per field; updating an excluded field
  (password) produces a row with no old/new values; toggling `is_active` produces
  `activated`/`deactivated` instead of a generic field-diff row; creating a record produces one
  `created` row; deleting a record produces one `deleted` row; an update triggered with no
  authenticated user (e.g. from a seeder) doesn't throw and stores a null `user_id`.
- Per-domain smoke tests: at least one real save through each domain's actual controller/service
  path produces the expected `audit_logs` rows — catches any place a domain's controller bypasses
  Eloquent's normal save path (e.g. raw `DB::table()` updates would not fire model events and
  would silently produce no audit trail; worth explicitly checking each domain's controller for
  this pattern before wiring up its trait).
- UI: the shared audit-trail component renders correctly with real data; the permission map
  correctly denies a user who lacks that domain's `.view-audit` permission (403, not just a hidden
  button).

## Out of Scope

- Retention/archival policy for `audit_logs` — grows unbounded for now, same as this app's other
  append-only tables (`job_audit_logs` has no cleanup either).
- Restoring/reverting a record from its audit history — this is a read-only trail, not an undo
  system.
- Auditing Job Tracking's own `Job`/`JobPhoto` models — already covered by the existing, separate
  `job_audit_logs` system; not migrated or touched by this work.
- A single consolidated "all activity" page across every domain — the requirement is explicitly
  per-record, distributed into each section, not one global feed.
