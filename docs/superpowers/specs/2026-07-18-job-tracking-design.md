# Job Tracking Module — Design

## Context

The Oracle Machine Tech CRM has no concept of field/shop-floor work tracking today. This adds a
new, self-contained Job Tracking module: workers request or receive jobs (CNC machine work,
either in-house or at outside sites), complete them with photo + GPS proof, and every action is
recorded in an immutable audit trail. Built on top of the RBAC role matrix and the
Controller→Service→Repository architecture already established in this codebase (see
`docs/DEVELOPMENT-STANDARDS.md`). This is new development, not a refactor — it must not touch any
existing module, table, or route.

Confirmed with the user before design: this module is intentionally decoupled from Invoice/
Quotation — a "job" is a standalone unit of work, not tied to a specific order.

## Approach

Follow the existing architecture pattern exactly: `App\Http\Controllers\Job\*`,
`App\Services\Job\*`, `App\Repositories\Contracts\Job*Interface` /
`App\Repositories\Eloquent\EloquentJob*Repository`, bound in `RepositoryServiceProvider`, every
repository read piped through the existing no-op `TenantScope`. New Blade views reuse the design-
system component library already built this session (`<x-ui.button>`, `<x-ui.status-badge>`,
`<x-ui.confirm-modal>`, `<x-ui.empty-state>`, `<x-ui.data-table-card>`) rather than inventing new
UI patterns.

## Database Schema

### `machines` (new picklist for in-house equipment)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | e.g. "CNC Lathe #2", "Fiber Laser Cutter" |
| is_active | boolean, default true | soft-disable instead of delete — jobs reference it |
| timestamps | | |

### `jobs`

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| title | string | |
| description | text, nullable | |
| machine_id | FK → machines, nullable | set when work is in-house |
| site_name | string, nullable | free text, set when work is off-site ("outside"). Exactly one of `machine_id`/`site_name` is set — enforced in the service layer, not the DB (no DB-level XOR check across two nullable FKs in a portable way across MySQL/sqlite) |
| created_by | FK → users | |
| assigned_to | FK → users, nullable | worker requests default to themselves; manager sets/changes on approval or direct assignment |
| priority | enum: low/medium/high/urgent | default medium |
| due_date | date, nullable | |
| status | enum: pending_approval/assigned/in_progress/on_hold/completed/rejected | |
| decided_by | FK → users, nullable | manager who approved/rejected |
| decided_at | timestamp, nullable | |
| rejection_reason | text, nullable | **required** when status → rejected |
| on_hold_reason | text, nullable | **required** when status → on_hold (confirmed with user) |
| completion_notes | text, nullable | **required** when status → completed |
| completed_at | timestamp, nullable | auto-set by server, never client-supplied |
| timestamps | | |

### `job_photos` (multiple per job)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| job_id | FK → jobs | |
| uploaded_by | FK → users | |
| path | string | compressed copy on `storage/app/public/job-photos/`, resized (longest edge capped, e.g. 1600px) and JPEG-quality-reduced via PHP's built-in GD extension — no new composer dependency for this |
| latitude / longitude | decimal(10,7), nullable | from browser Geolocation API, submitted with the upload request |
| location_captured | boolean, default false | explicit flag for "location not captured" (GPS denied/unavailable) — never silently null |
| map_link | string, nullable | `https://www.google.com/maps?q={lat},{lng}` — free, no API key required |
| address | string, nullable | best-effort reverse geocode via OpenStreetMap Nominatim (free, no key, via existing `guzzlehttp/guzzle` dependency) — failure never blocks the upload, left null |
| captured_at | timestamp | **server-set** on receipt, not trusted from the client — prevents clock-backdating |
| timestamps | | |

### `job_audit_logs` (immutable)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| job_id | FK → jobs | |
| user_id | FK → users | the actor |
| action | string | created / approved / rejected / assigned / reassigned / status_changed / photo_uploaded / completed / notes_edited / on_hold / resumed |
| description | text | human-readable line, e.g. "Status changed from Assigned to In Progress" |
| metadata | json, nullable | structured old/new values for programmatic display |
| created_at | timestamp only | **no `updated_at` column exists on this table at all** |

**Tamper-proofing, enforced at the code level, not just "no UI for it":**
- No controller/service method for editing or deleting a `job_audit_logs` row will ever exist.
- The `JobAuditLog` model registers `static::updating()` / `static::deleting()` hooks that throw
  `\LogicException` unconditionally — so even a future accidental `->update()`/`->delete()` call
  anywhere in the codebase fails loudly instead of silently corrupting history.

### `push_subscriptions` (Web Push)

Standard shape required by `laravel-notification-channels/webpush`: `id`, `user_id` (FK →
users), `endpoint`, `public_key`, `auth_token`, `content_encoding`, timestamps.

### Relationships

- `User` hasMany `Job` via `created_by` (`createdJobs()`) and via `assigned_to` (`assignedJobs()`)
- `Job` belongsTo `Machine` (nullable), hasMany `JobPhoto`, hasMany `JobAuditLog`
- `Machine` hasMany `Job`

## Status Flow

```
Worker creates job  ──────────► pending_approval ──[manager approves]──► assigned
                                       │
                                       └──[manager rejects, reason required]──► rejected  (terminal)

Manager/Owner assigns directly ──────────────────────────────────────────────► assigned

assigned ──[worker starts]──► in_progress ──[reason required]──► on_hold ──[resume]──► in_progress
                                    │
                                    └──[notes + ≥1 photo required]──► completed  (terminal)
```

- **Reassignment** (manager changes `assigned_to`) is allowed from `assigned`, `in_progress`, or
  `on_hold`. Doesn't change status. Logged as `reassigned`.
- **Rejected and Completed are both terminal.** A rejected request means the worker files a new
  job if the work is still needed — no in-place resubmission path in this version.
- Only the assigned worker (or a manager/owner) can start/hold/resume/complete a job. Only a
  manager/owner can approve, reject, assign, or reassign.

## Permissions

Added to `Database\Seeders\RolesAndPermissionsSeeder::PERMISSIONS` (existing dynamic role system —
"Manager" is not a special-cased role, it's created by the Owner via the existing admin UI and
assigned whichever of these permissions apply):

- `jobs.view-own` — Worker: see own assigned + own created (including pending/rejected) jobs
- `jobs.create` — Worker: submit a job request
- `jobs.view-all` — Manager/Owner: see every job
- `jobs.approve` — Manager/Owner: approve/reject pending requests
- `jobs.assign` — Manager/Owner: assign/reassign
- `jobs.manage-machines` — Manager/Owner: maintain the machines picklist

`Owner` role gets all of them (matches the existing pattern — Owner is granted every permission
in the seeder). `Worker` role gets `jobs.view-own` + `jobs.create` by default.

## Photo Capture + GPS + Audit Log: Fraud-Deterrence Explanation

No single mechanism prevents fraud alone — the combination raises the bar for a web (non-native)
app:

1. **Camera-only capture** — the file input uses `capture="environment"`, which on mobile browsers
   opens the device camera directly rather than the photo gallery picker, so a worker can't submit
   an old photo taken elsewhere. This is a mobile-browser convention, not a hard guarantee — a
   desktop browser or a rooted/jailbroken device could circumvent it. Confirmed acceptable with
   the user as the realistic bar for a web app (vs. a native/PWA app with hardware attestation,
   which is out of scope).
2. **GPS captured client-side in the same submit** (`navigator.geolocation.getCurrentPosition`)
   fires as part of the same request as the photo upload — there's no separate "add location" step
   to skip, and no free-text field to fake an address into.
3. **Server-set timestamp** — `captured_at` is stamped by the server clock on receipt, never
   trusted from client input, preventing completion-time backdating via a manipulated device
   clock.
4. **Immutable audit log** ties the sequence together — every photo upload, status change, and
   completion is its own permanent row with actor + exact timestamp. A denied-GPS photo is
   flagged (`location_captured = false`) and visible to the reviewing manager, never silently
   hidden — the system surfaces the gap instead of masking it.

## Notifications

Both channels ship in this plan (confirmed with user, accepting the trade-offs below):

- **In-app bell** (baseline, always works): Laravel's built-in database notifications
  (`notifications` table, `Notifiable` trait already present on `User`), rendered into the
  topbar's existing notification dropdown — replacing its current dead Velzon demo content
  ("Angela Bernier" placeholder rows) as a byproduct.
- **Web Push** (enhancement, has real gaps): `laravel-notification-channels/webpush` package (the
  one new composer dependency this feature introduces), `push_subscriptions` table, a service
  worker file (`public/sw.js`), VAPID keypair in `.env`, subscribe prompt on first login post-
  launch. Requires HTTPS. **iOS only supports Web Push on 16.4+, and only if the site was added
  to the home screen** — plain Safari browsing never receives it. Android/Chrome/desktop have no
  such restriction. Both channels fire from one notification class
  (`App\Notifications\JobDecisionNotification`) — one event, two channels, no duplicated logic.

## Role-Based UI

**Worker (mobile-first):**
- "My Jobs" — status-colored cards (`<x-ui.status-badge>`), filter tabs: Assigned / In Progress /
  On Hold / Completed / My Requests (own pending/rejected, rejection reason shown inline).
- "Request a Job" — title, description, machine dropdown or "Outside" toggle → site name field,
  priority, optional due date (`<x-ui.button>`).
- Job detail — one primary action visible at a time based on current status (Start / Put On Hold
  / Resume / Complete), not a cluttered toolbar. Complete flow: notes textarea, camera-capture
  button, live "Location captured ✓ / not available ⚠" indicator shown before submit.

**Manager:**
- "All Jobs" — `<x-ui.data-table-card>` shell, filterable by worker/status/date/machine.
- "Pending Approval" queue — dedicated sidebar tab with a badge count; approve/reject inline
  (reject requires typing a reason, via `<x-ui.confirm-modal>`).
- Job detail — full audit-trail timeline, photo gallery with map link/address per photo, reassign
  dropdown.

**Owner:**
- Dashboard widget row added to the existing dashboard: jobs completed today, pending approval
  count, pending completion count, per-worker breakdown.
- Same full drill-down access as Manager (Owner already holds every permission by default).

## Out of Scope (this plan)

- Tying jobs to Invoice/Quotation records (confirmed: jobs are standalone).
- In-place resubmission of a rejected job request (worker creates a new request instead).
- Native/installable PWA app or any hardware-backed anti-fraud guarantee beyond the browser-level
  measures above.
- Multi-tenancy wiring (the `TenantScope` seam is used, per existing convention, but stays a
  no-op — same as every other domain).
