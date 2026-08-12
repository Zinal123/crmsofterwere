# Worker Offline Hardening & Digital Job Traveler — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the shop-floor Worker experience robust offline and safe on shared tablets, and let a job carry a reusable set of work-instruction steps. Three focused additions on top of already-shipped scaffolding: (1) Background Sync so queued offline photos upload even after the tab closes; (2) shared-tablet data isolation so one worker's cached pages/queued photos never leak to the next; (3) reusable **job traveler templates** that seed a job's checklist with standard steps.

**Architecture:** The offline photo outbox already exists (IndexedDB `oracle-crm-offline-photos` / store `pending_photos`, drained by `syncPendingPhotos()` in `resources/views/jobs/_detail.blade.php`, with an `online`-event + on-load + manual "Sync Now" trigger). We add the **Background Sync API** as a more-robust drain path that runs in the service worker (`public/sw.js`) even with no open tab, keeping the existing page-level sync as the fallback for browsers without Background Sync (notably iOS Safari). Offline records get tagged with the owning `userId` so the queue is per-worker; logout clears the worker-page SW cache and other users' records. The job traveler reuses the existing `job_checklist_items` (no parallel step model) — a new `checklist_templates` + `checklist_template_items` pair defines reusable step lists, and applying a template creates checklist items on the job.

**Tech Stack:** Laravel 10, PHP 8.2, MySQL (prod) / SQLite (tests), Blade + Bootstrap 5, vanilla JS + Service Worker + IndexedDB + Background Sync API, PHPUnit feature tests.

## Global Constraints

- **Do not lose un-synced photos.** No code path may delete a `pending_photos` record that has not been confirmed uploaded (HTTP ok/redirect). Logout flushing clears *page caches* and *other users'* records only; the current user's un-synced photos are synced-or-warned, never silently dropped.
- **Background Sync is additive, never the only path.** iOS Safari lacks Background Sync; the existing `online`-event + on-load + manual "Sync Now" drain must keep working unchanged as the fallback. Feature-detect (`'sync' in registration`) before using it.
- **Same-origin auth.** Service-worker replay fetches are same-origin and send the session cookie automatically. CSRF is satisfied by sending the token as the `X-CSRF-TOKEN` header (Laravel's `VerifyCsrfToken` accepts it) — the token is captured at queue time and stored with the record.
- Quantities/positions are integers. New permissions go through `RolesAndPermissionsSeeder` and are granted to `Owner`.
- Tests run with `php artisan test` (SQLite in-memory); reuse `RolesAndPermissionsSeeder` in `setUp()`. FK columns are `foreignId`/`unsignedBigInteger`, never `integer` (this app had a clean-migrate failure from `integer` FKs).
- Every commit message ends with: `Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>`.
- **Testing reality:** Service-worker / IndexedDB / Background-Sync behavior is not exercisable by PHPUnit and this app has no browser-test harness. Those tasks end with a scripted **manual verification** in Chrome DevTools (steps given) instead of an automated test. PHP-side tasks (Phase 3, and any endpoint changes) use full TDD.

## Current State (verified 2026-08-12)

- `public/sw.js`: caches `/portal` login fallback and worker `/jobs` pages (`omt-worker-kiosk-v1`, network-first-then-cache); has `push` + `notificationclick` handlers; **no `sync` handler, no cache-clear message handler.**
- `resources/views/jobs/_detail.blade.php`: camera-enforced capture (getUserMedia + canvas annotation, file-input fallback), GPS lat/lng, IndexedDB queue helpers (`openOfflineDb`, `queuePhotoOffline`, `getPendingPhotosForJob`, `removePendingPhoto`), `uploadPhotoBlob`, `syncPendingPhotos`, `renderOfflineQueuePanel`; drains on `online`, on load if `navigator.onLine`, and via `#sync-now-btn`. Queued records currently carry `{ jobId, blob, latitude, longitude, stage }` — **no userId, no csrf/url.**
- `resources/views/layouts/worker.blade.php`: registers the service worker; has a Log Out form (`route('logout')`) and a high-contrast toggle.
- `EnforceIdleTimeout` middleware: shared/untrusted sessions auto-logout after 15 min; personal (device_trusted) exempt. (Dual-mode lockout already done.)
- `job_checklist_items`: `job_id, description, is_completed, completed_by, completed_at`. `JobChecklistService::addItem/toggleItem`. Ordered by id.
- Photo upload endpoint: `route('jobs.photos.store', $job->id)` (POST, `enctype=multipart/form-data`, fields `photo, stage, latitude, longitude`), a normal web route (CSRF-protected).

## File Structure

- Modify `resources/views/jobs/_detail.blade.php` — enrich queued records (userId, csrfToken, url, stage, lat/lng), register Background Sync on queue, scope queue reads to current user.
- Modify `public/sw.js` — add `sync` event (drain outbox) + a `message` handler (clear worker cache), plus a `postMessage` to clients after a background drain.
- Modify `resources/views/layouts/worker.blade.php` — flush-on-logout wiring (final sync + clear SW cache + clear other-user records), expose current user id + csrf token to JS.
- Create `database/migrations/*_create_checklist_templates_table.php` + `*_create_checklist_template_items_table.php`; `app/Models/ChecklistTemplate.php`, `app/Models/ChecklistTemplateItem.php`.
- Create `app/Services/Job/ChecklistTemplateService.php` (CRUD + `applyToJob`).
- Modify `app/Http/Controllers/Job/JobController.php` (+ a new `Admin\ChecklistTemplateController` or fold into an existing admin controller) + `routes/web.php` + `RolesAndPermissionsSeeder` + views (`resources/views/admin/checklist-templates/*`, and an "Apply template" control in `jobs/_detail.blade.php`).
- Tests under `tests/Feature/Job/`.

---

## Phase 1 — Background Sync for the offline photo outbox

### Task 1: Enrich queued records + register a background sync when queuing

**Files:**
- Modify: `resources/views/jobs/_detail.blade.php`
- Modify: `resources/views/layouts/worker.blade.php` (expose `<meta name="csrf-token">` and current user id if not already present)

**Interfaces:**
- Produces: queued `pending_photos` records now include `{ jobId, blob, latitude, longitude, stage, userId, csrfToken, url, queuedAt }`. A background sync is requested with tag `omt-photo-outbox` after each successful queue (feature-detected).

- [ ] **Step 1: Ensure the page can read a CSRF token and the current user id.** In `resources/views/layouts/worker.blade.php` `<head>`, add (if absent):
```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="current-user-id" content="{{ auth()->id() }}">
```

- [ ] **Step 2: In `jobs/_detail.blade.php`, capture the token/url/userId once near the offline-queue script:**
```js
var OMT_CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
var OMT_USER_ID = (document.querySelector('meta[name="current-user-id"]') || {}).content || '';
var OMT_UPLOAD_URL = uploadForm.action;
```

- [ ] **Step 3: Extend the record written by `queuePhotoOffline`** (find where records are built before `queuePhotoOffline(record)` and where `canvas.toBlob(...)` builds `meta`) so every queued record carries replay data:
```js
// when building the record/meta to queue:
{ jobId: jobId, blob: blob, latitude: lat, longitude: lng, stage: stageInput.value,
  userId: OMT_USER_ID, csrfToken: OMT_CSRF, url: OMT_UPLOAD_URL, queuedAt: Date.now() }
```

- [ ] **Step 4: After a photo is queued offline, request a background sync (feature-detected):**
```js
function requestBackgroundSync() {
    if ('serviceWorker' in navigator && navigator.serviceWorker.ready) {
        navigator.serviceWorker.ready.then(function (reg) {
            if ('sync' in reg) { reg.sync.register('omt-photo-outbox').catch(function () {}); }
        });
    }
}
// call requestBackgroundSync() immediately after queuePhotoOffline(record) resolves
```

- [ ] **Step 5: Manual verification** (no automated test — client-side only):
Run the app (`php artisan serve`), open a worker job detail page, DevTools → Application → Service Workers (ensure registered). Application → IndexedDB → `oracle-crm-offline-photos` → `pending_photos`. Go DevTools → Network → Offline, capture a photo, submit. Confirm a new record appears in `pending_photos` with `userId`, `csrfToken`, `url`, `stage`. In Application → Background Sync (or Console), confirm tag `omt-photo-outbox` was registered (no error).

- [ ] **Step 6: Commit**
```bash
git add resources/views/jobs/_detail.blade.php resources/views/layouts/worker.blade.php
git commit -m "feat: enrich offline photo records + register background sync

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

### Task 2: Service-worker `sync` handler drains the outbox

**Files:**
- Modify: `public/sw.js`

**Interfaces:**
- Consumes: the enriched `pending_photos` records (Task 1).
- Produces: on a `sync` event tagged `omt-photo-outbox`, the SW uploads each pending photo (same-origin fetch, cookie auth, `X-CSRF-TOKEN` from the record), deletes confirmed records, and `postMessage`s open clients `{ type: 'omt-outbox-drained' }`.

- [ ] **Step 1: Add IndexedDB helpers + the `sync` handler to `public/sw.js`** (mirror the DB name/store from the page):
```js
const OMT_OFFLINE_DB = 'oracle-crm-offline-photos';
const OMT_OFFLINE_STORE = 'pending_photos';

function omtOpenDb() {
    return new Promise(function (resolve, reject) {
        const req = indexedDB.open(OMT_OFFLINE_DB, 1);
        req.onsuccess = function () { resolve(req.result); };
        req.onerror = function () { reject(req.error); };
    });
}
function omtAllPending(db) {
    return new Promise(function (resolve, reject) {
        const tx = db.transaction(OMT_OFFLINE_STORE, 'readonly');
        const req = tx.objectStore(OMT_OFFLINE_STORE).getAll();
        req.onsuccess = function () { resolve(req.result || []); };
        req.onerror = function () { reject(req.error); };
    });
}
function omtDelete(db, id) {
    return new Promise(function (resolve) {
        const tx = db.transaction(OMT_OFFLINE_STORE, 'readwrite');
        tx.objectStore(OMT_OFFLINE_STORE).delete(id);
        tx.oncomplete = function () { resolve(); };
        tx.onerror = function () { resolve(); };
    });
}

async function omtDrainOutbox() {
    const db = await omtOpenDb();
    const records = await omtAllPending(db);
    for (const r of records) {
        const form = new FormData();
        form.append('photo', r.blob, 'proof-' + (r.queuedAt || Date.now()) + '.jpg');
        form.append('stage', r.stage || 'general');
        if (r.latitude) form.append('latitude', r.latitude);
        if (r.longitude) form.append('longitude', r.longitude);
        try {
            const resp = await fetch(r.url, {
                method: 'POST',
                body: form,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': r.csrfToken || '' },
            });
            if (resp.ok || resp.redirected) { await omtDelete(db, r.id); }
            else if (resp.status === 419 || resp.status === 401) { break; } // stale session/csrf — stop, page will retry on next login
        } catch (e) { throw e; } // still offline — let Background Sync retry later
    }
    const clientsList = await self.clients.matchAll();
    clientsList.forEach(function (c) { c.postMessage({ type: 'omt-outbox-drained' }); });
}

self.addEventListener('sync', function (event) {
    if (event.tag === 'omt-photo-outbox') { event.waitUntil(omtDrainOutbox()); }
});
```
(Note: throwing from `omtDrainOutbox` on a network failure makes the browser retry the `sync` later — intended. A 419/401 breaks the loop without deleting, so nothing is lost.)

- [ ] **Step 2: In `jobs/_detail.blade.php`, react to the drain message** so the open page refreshes its queue panel:
```js
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', function (e) {
        if (e.data && e.data.type === 'omt-outbox-drained') { renderOfflineQueuePanel(); }
    });
}
```

- [ ] **Step 3: Bump the SW cache version** (so the new SW activates): change `omt-worker-kiosk-v1` → `omt-worker-kiosk-v2` (and the portal cache if you touch it) and add an `activate` cleanup that deletes old caches. Verify the existing `activate` handler removes stale caches (add a `caches.keys()` sweep if absent).

- [ ] **Step 4: Manual verification:**
DevTools → Application → Service Workers → "Update on reload" + confirm the new SW (v2) activates. Offline: capture a photo (queues). Close the tab entirely. DevTools (from another page) or re-open: go online. In DevTools → Application → Background Sync, trigger/observe the `omt-photo-outbox` event (or just wait). Confirm the photo was uploaded (record removed from `pending_photos`, photo visible on the job after reload). Then repeat on a browser lacking Background Sync feel (or temporarily stub `'sync' in reg` to false) and confirm the `online`-event fallback still uploads.

- [ ] **Step 5: Commit**
```bash
git add public/sw.js resources/views/jobs/_detail.blade.php
git commit -m "feat: drain offline photo outbox via Background Sync

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

---

## Phase 2 — Shared-tablet data isolation

### Task 3: Scope the offline queue to the current worker

**Files:**
- Modify: `resources/views/jobs/_detail.blade.php`

**Interfaces:**
- Consumes: records now carry `userId` (Task 1).
- Produces: `getPendingPhotosForJob()` and the queue panel/count only ever surface records whose `userId === OMT_USER_ID`. Background drain (SW) still uploads all (they're all this device's), but the on-page view is per-user.

- [ ] **Step 1: Filter reads by current user.** In `getPendingPhotosForJob()` (or wherever pending records are listed for the panel), filter `records.filter(function (r) { return String(r.userId) === String(OMT_USER_ID); })`. Do the same in `renderOfflineQueuePanel` and `syncPendingPhotos` so a worker only sees/acts on their own queued photos.

- [ ] **Step 2: Manual verification:** Queue a photo as user A (offline). Confirm it shows in the panel. (Full cross-user check happens in Task 4.)

- [ ] **Step 3: Commit**
```bash
git add resources/views/jobs/_detail.blade.php
git commit -m "feat: scope offline photo queue to the current worker

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

### Task 4: Flush worker-page cache + other-user records on logout/login

**Files:**
- Modify: `public/sw.js` (add a `message` handler to clear the worker cache)
- Modify: `resources/views/layouts/worker.blade.php` (logout interception + login-time cleanup)

**Interfaces:**
- Produces: On logout from a Worker page, the worker-page SW cache is cleared and any *other* user's `pending_photos` records are deleted; the current user's un-synced photos trigger a **confirm** ("N photo(s) not yet uploaded — sync now before logging out?") rather than being deleted. On page load, if the last-seen user id differs from the current, other-user records + worker cache are cleared.

- [ ] **Step 1: SW cache-clear message handler** in `public/sw.js`:
```js
self.addEventListener('message', function (event) {
    if (event.data && event.data.type === 'omt-clear-worker-cache') {
        event.waitUntil(caches.delete('omt-worker-kiosk-v2'));
    }
});
```

- [ ] **Step 2: In `worker.blade.php`, intercept logout** (the Log Out form): on submit, (a) attempt a final sync of the current user's queue; (b) if un-synced records remain, `confirm()` — if the user cancels, abort logout; (c) delete other users' records; (d) `postMessage` the SW to clear the worker cache; then submit the form. Provide a helper that opens the DB, splits records by `userId`, deletes non-current, and counts current. Keep it defensive (IndexedDB may be unavailable → just proceed with logout).

- [ ] **Step 3: Login-time cleanup.** On worker page load, read `localStorage['omt-last-user']`; if it exists and differs from the current user id, clear the worker SW cache (postMessage) and delete all `pending_photos` not belonging to the current user. Then set `localStorage['omt-last-user'] = OMT_USER_ID`.

- [ ] **Step 4: Manual verification (the key security test):**
As Worker A on a shared browser profile: open a job (caches the page), capture a photo offline (queues under A). Log out (confirm the "not yet uploaded" prompt appears; choose to proceed for the test, or sync first). Log in as Worker B. Confirm: (1) Worker A's cached job page is no longer served from cache offline (Application → Cache Storage shows `omt-worker-kiosk-v2` emptied/rebuilt), (2) `pending_photos` contains no records with A's `userId`, (3) B's queue panel is empty. Then verify the no-data-loss path: as A, queue a photo, log out, cancel at the prompt → still logged in, photo intact.

- [ ] **Step 5: Commit**
```bash
git add public/sw.js resources/views/layouts/worker.blade.php
git commit -m "feat: isolate shared-tablet data — flush cache/other-user queue on logout

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

---

## Phase 3 — Digital job traveler (reusable checklist templates)

### Task 5: checklist_templates + checklist_template_items tables & models

**Files:**
- Create: `database/migrations/2026_08_15_000000_create_checklist_templates_table.php`
- Create: `database/migrations/2026_08_15_000001_create_checklist_template_items_table.php`
- Create: `app/Models/ChecklistTemplate.php`, `app/Models/ChecklistTemplateItem.php`
- Test: `tests/Feature/Job/ChecklistTemplateTest.php`

**Interfaces:**
- Produces: `ChecklistTemplate` (`name`, `is_active`) `hasMany` `ChecklistTemplateItem` (`checklist_template_id`, `description`, `position`) ordered by `position`.

- [ ] **Step 1: Write the failing test**
```php
<?php
namespace Tests\Feature\Job;

use App\Models\ChecklistTemplate;
use App\Models\ChecklistTemplateItem;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_a_template_has_ordered_items(): void
    {
        $t = ChecklistTemplate::create(['name' => 'Nozzle service', 'is_active' => true]);
        ChecklistTemplateItem::create(['checklist_template_id' => $t->id, 'description' => 'Power off', 'position' => 2]);
        ChecklistTemplateItem::create(['checklist_template_id' => $t->id, 'description' => 'Lock out', 'position' => 1]);

        $descriptions = $t->fresh()->items->pluck('description')->all();
        $this->assertSame(['Lock out', 'Power off'], $descriptions);
    }
}
```

- [ ] **Step 2: Run — verify FAIL.** `php artisan test --filter=ChecklistTemplateTest` → tables/models missing.

- [ ] **Step 3: Migrations**
```php
// create_checklist_templates_table
Schema::create('checklist_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
// create_checklist_template_items_table
Schema::create('checklist_template_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('checklist_template_id')->constrained('checklist_templates')->cascadeOnDelete();
    $table->string('description');
    $table->unsignedInteger('position')->default(0);
    $table->timestamps();
});
```

- [ ] **Step 4: Models** (`use App\Support\Auditing\Auditable;`)
```php
// ChecklistTemplate
class ChecklistTemplate extends Model {
    use Auditable;
    protected $fillable = ['name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(ChecklistTemplateItem::class)->orderBy('position');
    }
}
// ChecklistTemplateItem
class ChecklistTemplateItem extends Model {
    use Auditable;
    protected $fillable = ['checklist_template_id', 'description', 'position'];
    protected $casts = ['position' => 'integer'];
    public function template(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }
}
```

- [ ] **Step 5: Run — verify PASS.** `php artisan test --filter=ChecklistTemplateTest`.

- [ ] **Step 6: Commit**
```bash
git add database/migrations/2026_08_15_00000*_*.php app/Models/ChecklistTemplate.php app/Models/ChecklistTemplateItem.php tests/Feature/Job/ChecklistTemplateTest.php
git commit -m "feat: checklist template tables + models (job traveler)

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

### Task 6: ChecklistTemplateService — CRUD + applyToJob

**Files:**
- Create: `app/Services/Job/ChecklistTemplateService.php`
- Test: `tests/Feature/Job/ChecklistTemplateApplyTest.php`

**Interfaces:**
- Consumes: `JobChecklistService::addItem(Job,string)` (existing) or writes `job_checklist_items` directly via `$job->checklistItems()->create(...)`.
- Produces: `applyToJob(ChecklistTemplate $template, Job $job): int` — creates one `JobChecklistItem` per active template item (in `position` order), returns the count added. Idempotency is NOT required (applying twice adds the steps twice — acceptable; the UI warns).

- [ ] **Step 1: Write the failing test**
```php
public function test_applying_a_template_adds_its_items_to_the_job_checklist(): void
{
    $job = \App\Models\Job::factory()->create();
    $t = \App\Models\ChecklistTemplate::create(['name' => 'Service', 'is_active' => true]);
    \App\Models\ChecklistTemplateItem::create(['checklist_template_id' => $t->id, 'description' => 'Step A', 'position' => 1]);
    \App\Models\ChecklistTemplateItem::create(['checklist_template_id' => $t->id, 'description' => 'Step B', 'position' => 2]);

    $count = app(\App\Services\Job\ChecklistTemplateService::class)->applyToJob($t, $job);

    $this->assertSame(2, $count);
    $this->assertSame(['Step A', 'Step B'], $job->fresh()->checklistItems->pluck('description')->all());
}
```
(Seed roles in setUp; `Job::factory()` exists.)

- [ ] **Step 2: Run — verify FAIL.**

- [ ] **Step 3: Implement**
```php
<?php
namespace App\Services\Job;

use App\Models\ChecklistTemplate;
use App\Models\Job;

class ChecklistTemplateService
{
    public function applyToJob(ChecklistTemplate $template, Job $job): int
    {
        $items = $template->items; // ordered by position
        foreach ($items as $item) {
            $job->checklistItems()->create(['description' => $item->description]);
        }
        return $items->count();
    }
}
```
(CRUD of templates themselves is thin — do it in the controller with Eloquent, or add `create/update/delete` methods here if you prefer; keep the service focused on `applyToJob` unless the controller grows.)

- [ ] **Step 4: Run — verify PASS.**

- [ ] **Step 5: Commit**
```bash
git add app/Services/Job/ChecklistTemplateService.php tests/Feature/Job/ChecklistTemplateApplyTest.php
git commit -m "feat: apply a checklist template to a job

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

### Task 7: Admin CRUD for templates (permission + routes + controller + views)

**Files:**
- Modify: `database/seeders/RolesAndPermissionsSeeder.php` — add `'checklist-templates.manage'` to `PERMISSIONS` (Owner gets it via `syncPermissions(self::PERMISSIONS)`).
- Create: `app/Http/Controllers/Admin/ChecklistTemplateController.php` (index/store/update/destroy + item add/remove)
- Modify: `routes/web.php` — an `admin/checklist-templates` route group gated `permission:checklist-templates.manage`
- Create: `resources/views/admin/checklist-templates/index.blade.php`
- Modify: `resources/views/layouts/sidebar.blade.php` — a nav link under Masters/Admin gated `@can('checklist-templates.manage')`
- Test: `tests/Feature/Job/ChecklistTemplateAdminTest.php`

**Interfaces:**
- Produces: routes `admin.checklist-templates.index/store/update/destroy` and `admin.checklist-templates.items.store/destroy` (or nested), all gated by `checklist-templates.manage`.

- [ ] **Step 1: Write failing tests** — Owner can create a template with items and see it on the index; a Worker (no `checklist-templates.manage`) is `assertForbidden()` on the index and store. (Use `$user->syncRoles(['Worker'])` for the worker — the UserFactory attaches Owner by default, a known gotcha, so `syncRoles` isolates the role.)

- [ ] **Step 2: Run — verify FAIL.**

- [ ] **Step 3: Implement** the permission, routes (mirror an existing admin resource such as `admin.vendors.*` in `routes/web.php` and `Admin\*Controller` for the pattern), controller (thin: validate, Eloquent CRUD, `applyToJob` not involved here), and the index view (list templates; per-template inline item add/remove; create-template form). Re-run `RolesAndPermissionsSeeder` in dev after: `php artisan db:seed --class=RolesAndPermissionsSeeder --force`.

- [ ] **Step 4: Run — verify PASS.** `php artisan test --filter=ChecklistTemplateAdminTest`.

- [ ] **Step 5: Commit**
```bash
git add database/seeders/RolesAndPermissionsSeeder.php app/Http/Controllers/Admin/ChecklistTemplateController.php routes/web.php resources/views/admin/checklist-templates/ resources/views/layouts/sidebar.blade.php tests/Feature/Job/ChecklistTemplateAdminTest.php
git commit -m "feat: admin CRUD for job checklist templates

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

### Task 8: "Apply template" control on the job

**Files:**
- Modify: `app/Http/Controllers/Job/JobController.php` — `applyTemplate($jobId)` action; pass `$checklistTemplates` (active) to the job view
- Modify: `routes/web.php` — `POST jobs/{job}/apply-template` named `jobs.apply-template`, gated `permission:jobs.assign` (whoever manages a job's steps) — confirm which permission fits; reuse the checklist item's existing gate (`addChecklistItem` authorizes creator OR `jobs.assign`).
- Modify: `resources/views/jobs/_detail.blade.php` — an "Apply traveler template" `<select>` + submit near the checklist card, gated to the same users who can add checklist items.
- Test: add to `tests/Feature/Job/ChecklistTemplateApplyTest.php`

**Interfaces:**
- Consumes: `ChecklistTemplateService::applyToJob` (Task 6), `ChecklistTemplate::where('is_active', true)` (Task 5).

- [ ] **Step 1: Write the failing HTTP test**
```php
public function test_owner_can_apply_a_template_to_a_job_over_http(): void
{
    $owner = \App\Models\User::factory()->create(); $owner->assignRole('Owner');
    $job = \App\Models\Job::factory()->create(['created_by' => $owner->id]);
    $t = \App\Models\ChecklistTemplate::create(['name' => 'Svc', 'is_active' => true]);
    \App\Models\ChecklistTemplateItem::create(['checklist_template_id' => $t->id, 'description' => 'Do X', 'position' => 1]);

    $this->actingAs($owner)->post(route('jobs.apply-template', $job->id), ['checklist_template_id' => $t->id])->assertRedirect();
    $this->assertDatabaseHas('job_checklist_items', ['job_id' => $job->id, 'description' => 'Do X']);
}
```

- [ ] **Step 2: Run — verify FAIL.**

- [ ] **Step 3: Implement** the route, `JobController::applyTemplate` (validate `checklist_template_id exists:checklist_templates,id`; find job; authorize like `addChecklistItem`; `app(ChecklistTemplateService::class)->applyToJob($template, $job)`; redirect back with success), the `$checklistTemplates` passed from `show()`, and the select+submit control in the view.

- [ ] **Step 4: Run — verify PASS**, then the full suite `php artisan test`.

- [ ] **Step 5: Commit**
```bash
git add app/Http/Controllers/Job/JobController.php routes/web.php resources/views/jobs/_detail.blade.php tests/Feature/Job/ChecklistTemplateApplyTest.php
git commit -m "feat: apply a traveler template to a job from the job page

Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>"
```

---

## Deployment notes

- New migrations (`checklist_templates`, `checklist_template_items`) → `php artisan migrate`.
- New permission (`checklist-templates.manage`) → re-run `RolesAndPermissionsSeeder`.
- The service worker cache version bump (`omt-worker-kiosk-v1` → `v2`) means clients pick up the new SW on next load; the `activate` cleanup removes the old cache.
- No server change to the photo-upload endpoint is required — the SW replay hits the same `jobs.photos.store` route with cookie auth + `X-CSRF-TOKEN` header.

## Self-Review notes

- **Scope coverage:** Background Sync (Tasks 1–2), shared-tablet isolation/flush (Tasks 3–4), richer job traveler via reusable templates (Tasks 5–8). The offline **status-action** outbox was explicitly de-scoped by the owner and is NOT in this plan.
- **No-data-loss constraint** honored: no path deletes an un-synced current-user record; logout warns instead (Task 4), and the SW breaks (not deletes) on 419/401 (Task 2).
- **Fallback constraint** honored: Background Sync is feature-detected and additive; the existing `online`/on-load/manual drain is untouched.
- **Type consistency:** `applyToJob(ChecklistTemplate, Job): int`; SW tag `omt-photo-outbox`; SW message types `omt-outbox-drained` / `omt-clear-worker-cache`; queue record shape `{ jobId, blob, latitude, longitude, stage, userId, csrfToken, url, queuedAt }` used identically in page (write) and SW (read).
- **Testing honesty:** SW/IndexedDB/Background-Sync tasks carry scripted DevTools manual verification (no PHPUnit path exists); all PHP tasks (Phase 3) are full TDD.
```
