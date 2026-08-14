# Visual Consistency Audit — 2026-08-12

Discovery only. No code was changed, deleted, or restyled while producing this
document. This is the inventory the human signs off on before any fix work
(D2+) starts.

Scope: Laravel 10 CRM at `_public_html (1)`, branch `meet-update`. Rebrand
(#4361EE primary / #0D1B48 sidebar / #F7941D accent) is delivered via
`public/build/css/brand-theme.css` (loaded by every layout through
`layouts.head-css`) plus `resources/views/layouts/partials/theme-overrides.blade.php`
(loaded **only** by `layouts.master` — this asymmetry is the root cause of
most findings below).

---

## Step 1 — Inventory

### 1a. Layouts and how many views extend each

```
46  @extends('layouts.master')
25  @extends('layouts.master-without-nav')
 6  @extends('layouts.client')
 2  @extends('layouts.worker')
```

`layouts.master` is the full admin shell (sidebar, topbar, `theme-overrides`
included). `layouts.master-without-nav` is the chrome-less shell used for
auth screens and error pages (`brand-theme.css` loads, `theme-overrides`
does **not**). `layouts.client` and `layouts.worker` are bespoke,
hand-built shells for the two non-admin surfaces (client portal, worker
kiosk) — neither includes `theme-overrides` either.

### 1b. Views not extending one of the four known layouts

Full `grep -rL` list (79 files) breaks down into three real categories —
this is not one finding, it's noise plus three genuine ones:

- **Partials/components/layout-internals meant to have no `@extends`** (the
  large majority): `layouts/*.blade.php` (master.blade.php itself, body,
  topbar, menu, sidebar, footer, head-css, vendor-scripts, customizer,
  layouts-detached/horizontal/two-column/vertical-hovered — unused
  Velzon layout variants, see B2), `components/ui/*.blade.php`,
  `components/breadcrumb.blade.php`, `auth/partials/hero.blade.php`,
  `auth/partials/styles.blade.php`, `worker/jobs/_summary.blade.php`,
  `jobs/_detail.blade.php`, `partials/modal-footer-buttons.blade.php`,
  `employees/_form.blade.php`. None of these are pages; no action needed.
- **PDF templates** (`pdf/invoice.blade.php`, `pdf/job.blade.php`,
  `pdf/quotation.blade.php`): correctly standalone, DomPDF output isn't part
  of the browsable shell.
- **`auth/passwords/confirm.blade.php`** — genuinely broken, not merely
  "doesn't extend a known layout". See B4-1.

### 1c. Hardcoded hex colors — full file:line list

See B3 below for the classified/actionable subset. Full raw grep result
(41 matches across 20 files):

```
resources/views/vender.blade.php:73            colors="primary:#405189,secondary:#f06548"   (lord-icon, Velzon default)
resources/views/paymenthistry.blade.php:67      colors="primary:#405189,secondary:#f06548"   (lord-icon, Velzon default)
resources/views/components/ui/confirm-modal.blade.php:6   colors="primary:#405189,secondary:#f06548"
resources/views/auth-404-alt.blade.php:24       colors="primary:#405189,secondary:#0ab39c"   (dead file, see B2)
resources/views/auth-logout-cover.blade.php:64  colors="primary:#405189,secondary:#08a88a"   (dead file, see B2)
resources/views/auth-logout-basic.blade.php:41  colors="primary:#405189,secondary:#08a88a"   (dead file, see B2)
resources/views/auth/passwords/reset.blade.php:46   colors="primary:#0ab39c"                 (live page, low priority)
resources/views/index.blade.php:319,336,349,366,376-378   chart JS hex (brand colors, duplicated)
resources/views/dashboard/worker.blade.php:133  chart JS hex (brand colors, duplicated)
resources/views/dashboard/manager.blade.php:147 chart JS hex (brand colors, duplicated)
resources/views/dashboard/account.blade.php:161,179,193   chart JS hex (brand colors, duplicated)
resources/views/text.blade.php:199,225,250      #e96c0e (off-brand orange; dead-ish file, see B2/B3)
resources/views/auth/partials/styles.blade.php:6,17,43,44,111,140,151,157,170,178,179,183,203,210
                                                 full brand palette re-declared in raw hex (see B4-2/B3)
resources/views/pdf/invoice.blade.php:11,19     #212529 / #f1f1f1 (Bootstrap defaults, print-only)
resources/views/pdf/quotation.blade.php:52,61   same
resources/views/pdf/job.blade.php:7,15          same
resources/views/layouts/client.blade.php:10     <meta name="theme-color" content="#4361ee">  (correct, on-brand, meta tag can't use CSS var)
resources/views/layouts/worker.blade.php:24,27  #f4f6f9 / #1d2939 (deliberate kiosk high-contrast palette per in-file comment)
resources/views/layouts/partials/theme-overrides.blade.php   the source-of-truth token file itself (expected)
resources/views/layouts/partials/command-palette.blade.php   var(--vz-*, #fallback) — safe, fallback-only pattern, not a drift risk
```

### 1d. Route → view mapping for auth, and the 5-role login/landing trace

**Route `login` (name `login`) resolves to `App\Http\Controllers\Auth\LoginController@showLoginForm`**
(the default Laravel `AuthenticatesUsers` trait, registered by
`Auth::routes(['register' => false])` at `routes/web.php:16`), which renders
`resources/views/auth/login.blade.php`. This is the **one shared staff
login** for Owner, Manager, Account, and Worker — there is no separate
worker login route/controller.

**Correction to the brief's stated premise:** the brief's "important
finding to resolve" states route `login` resolves to
`ClientLoginController::showLoginForm`. That is not what the code does.
`routes/web.php:57-59` does define a `GET login` on
`ClientLoginController`, but it sits inside
`Route::prefix('portal')->name('client.')->group(...)` (`routes/web.php:56`),
so its actual registered name is **`client.login`** (path `portal/login`),
not `login`. Confirmed directly against the router, not just static
reading:

```
$ php artisan route:list --name=login
GET|HEAD   login          login          › Auth\LoginController@showLoginForm
GET|HEAD   portal/login   client.login   › Client\Auth\ClientLoginController@showLoginForm
POST       portal/login   client.login.attempt › Client\Auth\ClientLoginController@login
```

So there **are** two separate login screens by design (staff vs. client,
separate guards) — the brief's premise that route `login` itself resolves
to the client controller is incorrect; the two are cleanly split by route
name, just as you'd want. The real question this audit answers instead is
whether those two screens are *visually* consistent with each other — see
B4-2.

**Per-role trace:**

| Role | Login screen | Route name | Post-login landing | Landing view | Layout |
|---|---|---|---|---|---|
| Owner | `auth/login.blade.php` | `login` | `/` (`root`) → `HomeController::root` | `index.blade.php` | `layouts.master` |
| Manager | `auth/login.blade.php` | `login` | `/` → `HomeController::root` | `dashboard/manager.blade.php` | `layouts.master` |
| Account | `auth/login.blade.php` | `login` | `/` → `HomeController::root` | `dashboard/account.blade.php` | `layouts.master` |
| Worker | `auth/login.blade.php` | `login` | `/` → `HomeController::root` | `dashboard/worker.blade.php` | `layouts.master` |
| Client | `client/auth/login.blade.php` | `client.login` | `portal/` (`client.dashboard`) → `Client\DashboardController::index` | `client/dashboard.blade.php` | `layouts.client` |

All four staff roles land on `layouts.master` (consistent with each other).
Client lands on a separate, hand-built `layouts.client` shell (own
sidebar-less topbar, own `<style>` block) — expected given it's a different
guard and a deliberately different portal experience, but see B4-2/B5 for
where that shell diverges from the brand tokens rather than just from the
admin layout.

**Notable wrinkle inside the Worker role itself** (not a login/landing
issue, but directly relevant to "visual consistency across a role's
session"): `dashboard/worker.blade.php` (the Worker's landing page) uses
`layouts.master` — the full admin shell, sidebar and all. But
`JobController::index()` (`app/Http/Controllers/Job/JobController.php:42,55`)
sends a Worker-only user (permission `jobs.view-own` without
`jobs.view-all`) to `worker.jobs.index`, which extends `layouts.worker` — a
completely different, deliberately no-sidebar/high-contrast kiosk shell
(comment in that file: "deliberately not the admin theme"). So a Worker's
very first page after login is the full desktop admin chrome, and their
very next click (into their own Jobs list) drops them into a different
kiosk-styled shell with none of that chrome. Whether that's intentional
(dashboard = quick glance, jobs = shop-floor kiosk use) or a genuine
inconsistency is a product-design question, not something this discovery
task decides — flagging it as B4-3 for human triage either way.

---

## Step 2 — Remediation buckets

### B2 — Dead template files (route-unreachable, safe delete candidates)

Method: every file below was checked with `grep` against all of
`routes/web.php` and every controller for `view('<name>')`,
`route('<name>')`, `url('<name>')`, and `href="<name>"` — zero hits in the
live route/controller graph. The only references found are these files
linking to each other (leftover Velzon demo cross-links).

**Important caveat that changes their risk level, not their deadness:**
`routes/web.php:74` registers `Route::get('{any}', [HomeController::class, 'index'])->name('index')` with **no auth middleware**, and `HomeController::index()`
(`app/Http/Controllers/Home/HomeController.php:30-36`) does:
```php
if (view()->exists($request->path())) {
    return view($request->path());
}
return abort(404);
```
Any top-level `resources/views/*.blade.php` whose filename matches a URL
path segment is therefore renderable by anyone, unauthenticated, by
guessing the URL — these aren't just dead weight, they're an
unauthenticated info/UI leak of leftover demo content. Confirmed dead AND
technically publicly reachable via this catch-all:

- `resources/views/auth-404-alt.blade.php`
- `resources/views/auth-404-basic.blade.php`
- `resources/views/auth-404-cover.blade.php`
- `resources/views/auth-500.blade.php`
- `resources/views/auth-lockscreen-basic.blade.php`
- `resources/views/auth-lockscreen-cover.blade.php`
- `resources/views/auth-logout-basic.blade.php`
- `resources/views/auth-logout-cover.blade.php`
- `resources/views/auth-offline.blade.php`
- `resources/views/auth-signin-basic.blade.php`
- `resources/views/auth-signin-cover.blade.php`
- `resources/views/auth-signup-basic.blade.php`
- `resources/views/auth-signup-cover.blade.php`
- `resources/views/text.blade.php` (old invoice-detail template variant;
  extends `layouts.master`, has off-brand `#e96c0e` orange, reachable at
  `/text` for a guest — worst of both worlds: dead AND off-brand AND
  exposed)

Real error handling already exists and is properly branded
(`resources/views/errors/{403,404,419,500}.blade.php`, all
`@extends('layouts.master-without-nav')`), confirming the `auth-404-*` /
`auth-500` / `auth-offline` files are pure orphaned duplicates, not a
fallback anything depends on.

Also dead-by-the-same-method, lower urgency (unused Velzon layout
variants, referenced by nothing):
- `resources/views/layouts/layouts-detached.blade.php`
- `resources/views/layouts/layouts-two-column.blade.php`
- `resources/views/layouts/layouts-vertical-hovered.blade.php`
- `resources/views/layouts/layouts-horizontal.blade.php`

Not dead — confirmed live via controller/route wiring despite not matching
the `@extends` layout grep or having unusual names (excluded from B2):
`product.blade.php`, `vender.blade.php`, `paymenthistry.blade.php`,
`qutation.blade.php`, `listquation.blade.php`, `standerconfig.blade.php`,
`standerconfiglist.blade.php`, `technicalparameters.blade.php`,
`apps-invoices-details.blade.php`, `apps-invoices-list.blade.php`,
`apps-invoices-create.blade.php`.

### B3 — Hardcoded colors to tokenize

1. **`resources/views/auth/partials/styles.blade.php`** (lines 6, 17, 43,
   44, 111, 140, 151, 157, 170, 178, 179, 183, 203, 210) — the entire brand
   palette (`#F0F4FF` body bg, `#0D1B48` sidebar navy, `#F7941D` accent,
   `#4361EE` primary, plus `#6B7BB8`/`#93A0C9`/`#E4EAFB`/`#EEF2FF` secondary
   tones) is re-declared in raw hex here instead of consuming the
   `--vz-*` custom properties `theme-overrides.blade.php` already defines.
   **Highest-priority B3 item**: this file is `@include`d by both
   `auth/login.blade.php` and `client/auth/login.blade.php` — i.e. it's the
   CSS for every login screen in the app — and because
   `master-without-nav` never loads `theme-overrides.blade.php` (see
   scope note at top), this file is currently the *only* place these
   tokens exist on the login screens. If the brand palette changes
   centrally, both logins silently drift out of sync with the rest of the
   app. Should become `var(--vz-primary)`, a new `--vz-sidebar-bg`-style
   token, etc., sourced from the same file `theme-overrides.blade.php`
   uses, and that file (or its tokens) should be included on
   `master-without-nav` too.
2. **`resources/views/components/ui/confirm-modal.blade.php:6`** —
   lord-icon `colors="primary:#405189,secondary:#f06548"` is the Velzon
   default indigo/coral, not the brand palette. Shared component used by
   every delete-confirmation modal app-wide — one fix here fixes it
   everywhere it's used.
3. **`resources/views/vender.blade.php:73`** and
   **`resources/views/paymenthistry.blade.php:67`** — same off-brand
   lord-icon colors, on two live, route-reachable pages.
4. **`resources/views/text.blade.php:199,225,250`** — `#e96c0e` (off-brand
   orange, close to but not `#F7941D`). Only relevant if B2's decision on
   this file is "keep" rather than "delete"; noted here in case.
5. **Chart JS hex, duplicated 4x**: `resources/views/index.blade.php`
   (lines 319, 336, 349, 366, 376-378), `dashboard/manager.blade.php:147`,
   `dashboard/account.blade.php:161,179,193`,
   `dashboard/worker.blade.php:133` — all already use the correct brand
   hexes (`#4361EE`, `#F7941D`) plus a shared extended chart palette
   (`#10B981`, `#7B2FBE`, `#EF4444`, `#0EA5E9`, `#F59E0B`), but the same
   literal array is copy-pasted across all four dashboard views. Lower
   priority than items 1-3 (colors are already correct, not off-brand) —
   candidate for extraction into one shared JS constant rather than a CSS
   token, so the four dashboards can't drift from each other.
6. **`resources/views/layouts/worker.blade.php:24,27`** (`#f4f6f9`,
   `#1d2939`) — deliberately a different, high-contrast kiosk palette per
   the in-file comment ("deliberately not the admin theme"). Lowest
   priority; flagging only so it isn't accidentally "fixed" into matching
   the admin palette during B3 work — that would defeat its purpose.
7. **PDF templates** (`pdf/invoice.blade.php:11,19`,
   `pdf/quotation.blade.php:52,61`, `pdf/job.blade.php:7,15`) — Bootstrap
   default greys (`#212529`, `#f1f1f1`), not brand colors, print-only
   output. Lowest priority / arguably out of scope for a screen-theme
   tokenization pass.

### B4 — Login/landing consistency fixes

1. **`resources/views/auth/passwords/confirm.blade.php:1`** is
   `@@extends('layouts.master-without-nav')` — **double `@`**, which Blade
   treats as an escaped literal, not a directive. This view does not
   extend any layout at all: it has no `<html>`/`<head>`, so visiting it
   renders a bare, completely unstyled fragment with the literal text
   `@extends('layouts.master-without-nav')` printed at the top of the
   page. The route (`GET password/confirm`, name `password.confirm`) is
   live and registered (confirmed via `php artisan route:list --name=password`),
   reachable by any authenticated user who navigates to that URL, even
   though nothing in the app currently links to it or applies the
   `password.confirm` middleware to force the flow. This is the single
   clearest, highest-confidence bug found in this audit — one character
   fix (`@@` → `@`), but flagged here rather than fixed, per scope.
2. **`resources/views/auth/passwords/reset.blade.php:95`** — the real,
   currently-used "forgot password" page has a stray
   `<a href="auth-signin-basic">Click here</a>` link pointing at one of the
   dead Velzon demo pages from B2, not at the real `route('login')`. A
   real user on a real branded page can click through into unstyled
   Velzon demo cruft. High-confidence, concrete fix target.
3. **`client/auth/login.blade.php` vs. `auth/login.blade.php`** — both
   extend `layouts.master-without-nav` and both `@include('auth.partials.styles')`,
   but they use different root markup: the staff login wraps its content
   in `<div class="oms-auth-shell">` and additionally includes
   `auth.partials.hero` (the two-column layout with the gradient/glow hero
   panel, `.oms-auth-panel`, `.oms-auth-hero*` classes — all defined in
   `styles.blade.php`). The client login instead wraps in a bare
   `<div class="oms-auth">` with **no corresponding CSS rule anywhere** in
   `styles.blade.php` (only `.oms-auth-shell` / `.oms-auth-panel` /
   `.oms-auth-hero` exist, not `.oms-auth`) and never includes the hero
   partial. Net effect: staff gets the full split-screen hero layout,
   client gets a plain, unconstrained-wrapper card with no matching
   layout rule for its wrapper div. This is the concrete, file-level
   answer to "do all 5 roles get a visually consistent login" — Owner /
   Manager / Account / Worker (all through `auth/login.blade.php`) do; the
   Client (`client/auth/login.blade.php`) does not match them.
4. **Worker landing (`layouts.master`) vs. Worker's own Jobs list
   (`layouts.worker`)** — documented in the Step 1d table/note above.
   Included in this bucket as a candidate to review, not a clear bug: may
   be intentional UX split (desktop-style dashboard glance vs. shop-floor
   kiosk work list) rather than an inconsistency to fix.
5. **`theme-overrides.blade.php` is only `@include`d by `layouts.master`**
   (confirmed via `grep -rn "theme-overrides" resources/views` — one
   result, `layouts/master.blade.php:14`). `layouts.master-without-nav`
   (all auth screens + error pages), `layouts.client`, and `layouts.worker`
   never load it. They all still get the primary-color override via
   `head-css.blade.php` → `brand-theme.css` (loaded everywhere), but they
   never get the sidebar-navy / accent-orange / body-bg / border-radius
   tokens `theme-overrides.blade.php` defines. This is the structural
   root cause behind B4-3's client-login styling gap and is why
   `auth/partials/styles.blade.php` had to hand-roll the palette in B3-1.
   Fixing this (moving the shared tokens somewhere all four layouts
   include, or including `theme-overrides` more broadly) would likely
   resolve B3-1 and B4-3 together rather than needing two separate patches.

### B5 — Per-page shell/component inconsistencies (spot-check, not exhaustive)

Confirmed-good references used for comparison:
`admin/checklist-templates/index.blade.php` and `vendor/index.blade.php`
(brief named `admin/vendors/index.blade.php`; that path doesn't exist —
the real vendor index lives at `resources/views/vendor/index.blade.php`
and does use `<x-ui.data-table-card>`/`<x-ui.button>`/`<x-ui.empty-state>`/
`<x-ui.confirm-modal>` consistently, so it still serves as a good
reference).

Sample of admin/CRUD-style index pages checked for
`<x-ui.data-table-card>` usage:

| View | Uses `x-ui.data-table-card` | Note |
|---|---|---|
| `employees/index.blade.php` | yes | consistent |
| `machines/index.blade.php` | yes | consistent |
| `expenses/index.blade.php` | yes | consistent |
| `expenses/categories/index.blade.php` | yes | consistent |
| `jobs/index.blade.php` | yes | consistent |
| `product.blade.php` | yes | consistent |
| `vender.blade.php` | yes | consistent (color issue only, see B3) |
| `paymenthistry.blade.php` | yes | consistent (color issue only, see B3) |
| `apps-invoices-list.blade.php` | yes | consistent |
| `ticketing/tickets/index.blade.php` | yes | consistent |
| `ticketing/client-accounts/index.blade.php` | yes | consistent |
| `ticketing/client-machines/index.blade.php` | yes | consistent |
| `ticketing/spare-part-requests/index.blade.php` | yes | consistent |
| `ticketing/problem-types/index.blade.php` | yes | consistent |
| `worker/jobs/index.blade.php` | no | expected — kiosk shell, not an admin CRUD page |
| `client/tickets/index.blade.php` | no (uses `x-ui.back-link` instead) | expected — client portal, not admin CRUD |
| `client/spare-parts/index.blade.php` | no (uses `x-ui.back-link` instead) | expected — client portal, not admin CRUD |

**Result of the spot-check: no inconsistencies found among admin/CRUD
index pages** — every one sampled uses the shared `<x-ui.data-table-card>`
pattern like the two reference pages. The only pages without it are the
Worker kiosk and Client portal views, which are correctly on their own
deliberately-different shells, not an oversight. This bucket's main
finding is therefore in the two non-admin shells themselves (already
covered under B4), not in admin-page component drift.

---

## Files referenced in this audit

- `routes/web.php`
- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Controllers/Client/Auth/ClientLoginController.php`
- `app/Http/Controllers/Home/HomeController.php`
- `app/Http/Controllers/Job/JobController.php`
- `database/seeders/RolesAndPermissionsSeeder.php`
- `resources/views/auth/login.blade.php`
- `resources/views/client/auth/login.blade.php`
- `resources/views/auth/partials/styles.blade.php`
- `resources/views/auth/partials/hero.blade.php`
- `resources/views/auth/passwords/confirm.blade.php`
- `resources/views/auth/passwords/reset.blade.php`
- `resources/views/layouts/master.blade.php`
- `resources/views/layouts/master-without-nav.blade.php`
- `resources/views/layouts/client.blade.php`
- `resources/views/layouts/worker.blade.php`
- `resources/views/layouts/partials/theme-overrides.blade.php`
- `resources/views/layouts/head-css.blade.php`
- `public/build/css/brand-theme.css`
- `resources/views/index.blade.php`, `dashboard/manager.blade.php`,
  `dashboard/account.blade.php`, `dashboard/worker.blade.php`
- `resources/views/worker/jobs/index.blade.php`
- `resources/views/client/dashboard.blade.php`
- `resources/views/components/ui/confirm-modal.blade.php`
- `resources/views/vender.blade.php`, `paymenthistry.blade.php`, `text.blade.php`
- `resources/views/errors/{403,404,419,500}.blade.php`
- Top-level dead Velzon demo files listed under B2
