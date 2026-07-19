# Development Standards

These rules codify the patterns established and verified across the India-localization,
bug-fixing, and clean-architecture work done on `meet-update` through 2026-07-17. They apply
to all new development from this point forward, not just the domains already migrated to them.

## 1. Architecture: Controller → Service → Repository

Every domain follows this layering (established by the Product Configuration pilot, commit
`b3d3d8d`, and now applied to Product, Inventory, Quotation, Home/Auth, and Invoice):

- **Controller** (`app/Http/Controllers/{Domain}/*Controller.php`): constructor-injects the
  Service via a PHP 8 promoted property. Each action is 2-4 lines — call the service, return a
  view/redirect/JSON response. **No Eloquent calls in controllers, ever.**
- **Service** (`app/Services/{Domain}/*Service.php`): constructor-injects the repository
  *interface* (never the concrete class). One method per view-data-shape or per write action.
  Business logic (validation orchestration, multi-model transactions, calculations) lives here.
- **Repository interface** (`app/Repositories/Contracts/*Interface.php`) and **implementation**
  (`app/Repositories/Eloquent/Eloquent*Repository.php`): the only place Eloquent queries are
  written. Bind interface → implementation in `app/Providers/RepositoryServiceProvider.php`.
- **`TenantScope`** (`app/Support/Tenancy/TenantScope.php`): every repository *read* pipes
  through `$this->tenantScope->apply($query)`. It's currently a no-op — the single seam where
  multi-tenancy (`->where('company_id', ...)`) gets added later, instead of hunting through every
  repository when that day comes. Writes don't need to go through it (matches existing convention
  across every domain so far — don't invent partial coverage differently per domain).

**Before writing a new repository interface, check if an existing one already covers the model
you need** (e.g. `ProductRepositoryInterface`, `ProductConfigRepositoryInterface`). Reuse over
duplication — this session repeatedly found new domains needing product/config lookups that
already existed. Group tightly-coupled models under one repository interface rather than one
interface per table (e.g. `InvoiceRepositoryInterface` covers Invoice+Customer+Invoiceproduct+
Paidamount together, matching how `ProductConfigRepositoryInterface` covers 10 config tables).

## 2. Test-first-then-refactor, always

For any change to existing behavior (bug fix, refactor, architecture migration):

1. **Write a feature test that characterizes current behavior first**, and confirm it passes
   *against the unmodified code*. If the test doesn't pass against the original code, it isn't
   testing what you think it's testing.
2. Make the change.
3. **Re-run the same test — it must pass unchanged**, unless the change was an explicitly
   intended behavior change (see §4).
4. Run the full suite (`php artisan test`) to catch collateral effects.
5. Manual authenticated `curl` smoke test against the **real live database** as a final backstop
   — schema drift between migrations and reality, or sqlite/MySQL differences, won't show up in
   the in-memory test DB alone. Clean up any test data created this way.

Tests live in `tests/Feature/{Domain}Test.php`, use `RefreshDatabase` per-class (not globally on
`TestCase` — keep Unit tests fast), and use `database/factories/*Factory.php` for the models
under test (add factories incrementally, only for what the current work needs).

## 3. Preserve behavior exactly; document, don't drive-by-fix

When refactoring, replicate existing behavior **exactly** — including bugs, dead queries, and
quirks. Every domain refactor this session surfaced previously-unknown bugs (mismatched column
names, hardcoded IDs, dead controller variables). The discipline:

- **Fix nothing you weren't already asked to fix or that isn't blocking the work itself.**
- Add a short code comment at the exact spot explaining the quirk and why it's untouched.
- Log it in `docs/QA-Dev-Work-Log.xlsx` → **Known Issues (Not Fixed)** sheet (or **Bugs Fixed**
  if it's genuinely resolved) so it doesn't get silently lost or rediscovered from scratch later.
- If a fix is a **behavior change** (not just a refactor) — e.g. wrapping multi-model creation in
  `DB::transaction()` — flag it and get explicit sign-off before bundling it into the same commit
  as the refactor. Don't smuggle behavior changes in under a "refactor" label.

## 4. Schema changes go through migrations — no more live-DB-only changes

Every table now has a real migration (`database/migrations/`, backfilled 2026-07-17). From here
on:

- **New tables/columns: write a migration.** Never `ALTER TABLE` directly against the live DB
  again — that's exactly the gap that made testing impossible until this session's backfill.
- Migrations must work against **both** MySQL (live) and sqlite (`.env.testing`, in-memory,
  `RefreshDatabase`). Avoid raw driver-specific DDL (`DB::statement('ALTER TABLE ... MODIFY')`)
  unless guarded with `DB::connection()->getDriverName()`.
- When a migration's table already exists on the live DB from before this convention (shouldn't
  happen going forward, but if it ever does), register it as already-applied via a direct insert
  into the `migrations` table — never let a real `CREATE TABLE` migration run against a table
  that already exists. See `docs/QA-Dev-Work-Log.xlsx` → **Manual Commands & Server** for the
  precedent and reasoning.

## 5. Verification checklist (every change, no exceptions)

1. `php -l` on every changed/new PHP file.
2. New/updated feature test(s) pass.
3. `php artisan test` — full suite green, or any failure is a pre-existing one you can name and
   explain (not one your change introduced).
4. Manual authenticated `curl` smoke test of the affected route(s) against the real live DB.
5. Spot-check 2-3 unrelated pages for regressions.
6. Clean up any test data created against the live DB during manual verification.

## 6. Git and documentation hygiene

- Detailed, structured commit messages: what changed, why, what was verified. Future-you (or the
  next session) should be able to reconstruct the reasoning without re-reading the diff.
- Keep `docs/QA-Dev-Work-Log.xlsx` current — Summary, Bugs Fixed, Known Issues sheets at minimum
  — as part of finishing a unit of work, not as an afterthought. It went stale for over a week
  earlier in this project's history; don't let that happen again.
- `.gitignore` should exclude generated/cache artifacts (`storage/framework/{cache,sessions,
  views}/*`, `storage/logs/*.log`) — don't let ephemeral files become untracked-file noise or,
  worse, get committed.
- Never commit secrets (`.env`) unless explicitly instructed with full understanding of the risk
  (see the school-crm precedent — that was a deliberate, confirmed exception, not the default).

## 7. Scope discipline

- Match the size of a change to what was actually asked. Don't refactor, rename, or "clean up"
  code adjacent to a change unless it's necessary for that change or explicitly requested.
- Prefer editing existing files/patterns over introducing new ones. Before adding a new
  abstraction, check whether an existing service/repository/utility already does the job.
- No speculative features, config flags, or generalization for hypothetical future needs. Build
  what's asked; the `TenantScope` seam is the one deliberate exception (a cheap, explicit hook
  for a confirmed future need — multi-tenancy — not a general "just in case" abstraction).

## 8. Responsive markup checklist

The base layout (Velzon's sidebar/topbar/viewport meta) and every legacy DataTables-driven list
page are already responsive — a 2026-07-19 full-app audit found the only regressions came from
newer hand-written views skipping patterns the rest of the app already follows. Apply these to
every new table or form view:

- **Always wrap `<table>` in `<div class="table-responsive">...</div>`**, or use DataTables (which
  handles this itself) for anything with more than a couple of columns. A bare `<table>` with no
  wrapper will overflow the viewport on a phone with no way to reach the clipped columns.
- **Never hardcode pixel widths on table cells** (`style="width: 696px"` etc.). Let columns size to
  content/percentage inside the `.table-responsive` wrapper. A large enough sum of fixed-px column
  widths breaks the layout on desktop too, not just mobile.
- **Use Bootstrap's `col-*` grid classes for form layout**, not bare `<div>`s in a flex-wrap
  container. `col-md-*` collapses to full-width below the `md` breakpoint automatically; flex-wrap
  divs with no explicit column width wrap unpredictably instead of deliberately.
- The base layout, viewport meta tag, and sidebar/topbar mobile-collapse behavior are stock Velzon
  — don't modify them to "fix" a responsiveness issue; the issue is almost always in the page's own
  markup, not the shell.
