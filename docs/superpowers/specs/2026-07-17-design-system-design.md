# Design System & UI/UX Consistency Pass — Design Spec

**Date:** 2026-07-17
**Status:** Approved, ready for implementation planning
**Scope:** UI/UX consistency and design-system foundation applied to the *existing, already-built* modules of the Oracle Machine Tech CRM. Does not touch business logic, routes, or data — visual/interaction layer only. Role-based dashboards (Owner/Manager/Operator/Staff) and any new MES modules (job orders, machine monitoring, attendance, maintenance) are explicitly deferred to later, separate design passes — see "Out of scope."

## Context

The user's original ask was large: a full design system, navigation/IA, role-based dashboards, and production code across a shop-floor MES vision (job orders, machine monitoring, staff attendance, maintenance scheduling) layered onto this CRM. That's a major product expansion spanning several independent, not-yet-built subsystems — too large for one spec, and premature: none of the new MES data models exist yet, so any dashboard or navigation design for them today would be speculative.

Per the user's explicit direction, this spec covers the deliberately-scoped *first* piece: get the UI/UX right on what's already built, establish a design system that carries forward unchanged into future modules, and defer role-based dashboards to last (once there's more to actually put on them).

**Audit findings that ground this spec** (full detail from a live codebase reconnaissance pass):
- Velzon (the underlying Bootstrap 5 admin template) already ships a complete SCSS variable system — colors, typography scale, dark mode — but the app has never extended it. `resources/scss/custom.scss` is a 7-line boilerplate stub; `build/css/custom.min.css` compiles to 0 bytes. Every visual decision so far has been ad hoc, per-view.
- **5 different "Create" button treatments** across 5 modules (colors: danger/primary/success interchangeably for the same non-destructive action; sizes: explicit `btn-md`, `btn-sm`, or unspecified).
- **3 different table technologies** in one app: server-side DataTables (5 views, loaded from CDN rather than the already-vendored local copy), client-side `list.js` (1 view), and plain unpaginated HTML tables with no search/sort at all (the 2 newest admin pages, built this session).
- **Status badges are color-only**, never color+icon, and the color semantics aren't standardized (Pending uses `warning`, Inactive uses `danger` — no documented rule for which "bad" states get which color).
- **Zero empty-state UI anywhere** in the codebase (a blank table with just a header row is what users see today).
- **No loading feedback** on any AJAX table load (`processing: true` is never set).
- **Error pages exist but aren't wired up** — Velzon-styled 404/500 templates sit in `resources/views/error/` (unused) while Laravel's actual exception handler looks in `resources/views/errors/` (doesn't exist), so real 403/404/500 hits show Laravel's default unstyled page. This is a live, current gap — the role-matrix feature just added permission middleware that throws 403s users will actually encounter.
- **Icon fragmentation**: Remix Icon (`ri-*`) is dominant (127 uses/21 files) but Material Design Icons (`mdi-*`) and Boxicons (`bx-*`) remain in the topbar and auth/error chrome — leftover, unreplaced Velzon scaffolding.
- Accessibility: icon-only action buttons (a bulk-delete pattern duplicated across 4+ modules) have no `aria-label`.

## Approach

Extend Velzon's existing design foundation rather than replace it. Two alternative approaches were considered and rejected: a full visual re-theme (higher risk, doesn't fit "don't break existing functionality"), and minimal targeted fixes with no reusable components (fast, but gives future modules nothing to inherit — the next module just invents pattern #6). This spec's approach is the only one of the three that satisfies the explicit requirement that the design system "carries forward" so the software never needs a full redo.

## Design tokens

**Colors** — no new palette. Codify semantic usage rules for Velzon's existing Bootstrap variables, since the audit found these violated repeatedly:

| Semantic role | Color | Use for |
|---|---|---|
| Success | `$success` (`#0ab39c`, green) | Positive states (Paid, Active, Completed) **and all Create/Add actions** |
| Warning | `$warning` (`#f7b84b`, yellow) | Needs-attention states (Pending, Low Stock) |
| Danger | `$danger` (`#f06548`, red) | Negative/destructive only (Inactive, Delete, Errors, Overdue) — never for additive actions |
| Primary | `$primary` (`#405189`, indigo) | Main navigation, primary Save/Submit actions |
| Info | `$info` (`#299cdb`, cyan) | Neutral in-progress/informational states |

This directly fixes: "Create Invoice" currently using `btn-danger` (a destructive color for an additive action), and the Active/Inactive badge using `danger` while Pending uses `warning` with no documented rule connecting them.

**Typography** — no new scale (Velzon's `fs-10` through `fs-36` utilities already cover everything needed). Codify which existing classes mean what:
- Page title: `h4.fs-22.fw-semibold.ff-secondary` (already the de facto standard on the dashboard — apply everywhere)
- Card/section title: `h5.card-title.mb-0`
- Metadata/secondary text: `fs-11`–`fs-12` + `text-muted`
- Table body text: default (no override needed)

**Spacing** — no new scale (Bootstrap's 0–5 spacing utilities, 0.25rem increments, already in use throughout). Codify consistent application: card padding, gap between toolbar action buttons, and card-to-card vertical rhythm on a page, all standardized to specific existing utility classes (documented in the component partials themselves, not as a separate abstract rule — see Components below).

**Icons** — standardize on Remix Icon (`ri-*`) for all CRM feature views (already dominant). Migrate `mdi-*`/`bx-*` icons in `layouts/topbar.blade.php` and the `auth-*`/`error` templates to `ri-*` equivalents (direct equivalents exist for every icon currently in use: search, moon/sun toggle, shopping bag, category, close).

## Component library

New Blade components under `resources/views/components/ui/`, each replacing a pattern currently copy-pasted with variations across modules:

- **`<x-ui.button>`** — props: `variant` (success/primary/danger/secondary), `size` (sm/md), `icon` (optional `ri-*` class), slot for label. Every "Create X" action becomes `<x-ui.button variant="success" icon="ri-add-line">Create Invoice</x-ui.button>`, fixing the 5-treatment inconsistency in one place.
- **`<x-ui.status-badge>`** — props: `status` (text), `variant` (success/warning/danger/info, following the semantic table above), `icon` (required — enforces the "never color alone" rule by construction, not convention). Replaces the raw `<span class="badge bg-success-subtle...">` strings currently hand-written in `InvoiceService.php` and `admin/users.blade.php`.
- **`<x-ui.data-table-card>`** — standardized card + table + toolbar shell (search box position, Create button position, consistent table classes). Used by every list page. DataTables-server-side views wrap their existing config in this shell without changing the underlying AJAX/pagination logic; the plain-table admin pages and the `list.js` product page adopt the same shell as part of the Phase 6 consolidation (see Rollout).
- **`<x-ui.empty-state>`** — props: `icon`, `message`, optional `action` slot (CTA button). Rendered inside a table body when zero rows, or wired as a DataTables `language.emptyTable` string styled to match.
- **`<x-ui.confirm-modal>`** — the delete-confirmation dialog already identically duplicated across invoices/inventory/vendor/paymenthistory, extracted to one component taking a `record-type` label prop.
- **Toast/feedback helper** — a small shared JS function (`window.notify(type, message)`) wrapping the already-loaded SweetAlert2, used after every AJAX form submit for success/error feedback. Fixes flows like Product create, which currently fails or succeeds with zero visible feedback to the user.

## States

- **Error pages**: move/adapt the existing (currently unused) `resources/views/error/auth-404-basic.blade.php` and `auth-500.blade.php` into `resources/views/errors/{404,500}.blade.php` (the directory Laravel's exception handler actually resolves), and add `403.blade.php` and `419.blade.php` (CSRF-token-expired — relevant for tablets left open on the shop floor) using the same visual template. This is a live, current gap: the role-matrix feature's permission middleware already throws 403s that hit Laravel's default unstyled error page today.
- **Loading states**: add `processing: true` plus a styled `language` config (loading/empty/search placeholder text) to every DataTables initialization.
- **Empty states**: `<x-ui.empty-state>` component, used both for zero-row tables and as the DataTables `emptyTable` language string.
- **Feedback**: the `window.notify()` toast helper described above, applied to every create/update/delete AJAX action across all modules.

## Accessibility baseline

- Every icon-only button gets `aria-label` — fixed once inside `<x-ui.button>` (an `icon`-only invocation requires an `aria-label` prop), applied everywhere at once instead of manually in 4+ files.
- `<x-ui.status-badge>` requires both `icon` and text — color is structurally never the only signal.
- No changes to font sizes or contrast ratios are needed — Velzon's defaults already meet WCAG AA for body text; this pass doesn't touch that.

## Prioritized rollout

Ordered by impact-to-risk ratio, per the explicit ask for "highest-impact improvements first." Each phase is independently shippable and testable — later phases don't block on earlier ones being "perfect," but do build on the components earlier phases create.

1. **Design tokens layer** — populate `custom.scss` with the semantic color/typography/spacing documentation above (as SCSS comments + any needed variable aliases). Zero visual risk on its own (documentation + non-overriding definitions); unblocks every later phase.
2. **Button + status-badge components**, retrofitted onto existing views. Fixes the single most visible inconsistency. Low risk — markup swap only, no route/controller/logic changes.
3. **Icon standardization** in the topbar and auth/error chrome. Contained, purely cosmetic, no functional risk.
4. **Error pages wired up.** Self-contained, currently a real live gap, low effort.
5. **Empty/loading states + toast feedback.** Moderate effort (touches every DataTables config + adds JS calls to every AJAX form handler), meaningfully improves perceived quality and directly fixes the Product-create-fails-silently gap.
6. **Table pattern consolidation** — migrate the plain-table admin pages (Roles, Users) and the `list.js`-based Product page onto the same server-side DataTables pattern already proven in 5 other modules, wrapped in `<x-ui.data-table-card>`. Highest effort and risk in this list (changes underlying JS/pagination mechanism on 2-3 pages), but delivers the biggest long-term win: real search/sort/pagination where currently completely absent, plus full visual consistency. Done last, with full test coverage per `docs/DEVELOPMENT-STANDARDS.md`'s test-first-then-refactor rule.

## Testing & verification approach

Per `docs/DEVELOPMENT-STANDARDS.md`: every phase gets `php -l` on changed files, a full `php artisan test` run (currently 56/56 passing — must stay green throughout, since this pass touches views/CSS/JS only, not routes or controllers, so no existing feature test should ever need to change), and a manual authenticated browser/curl smoke test of every page touched in that phase against real data. Phase 6 (table consolidation) additionally needs new feature tests confirming search/sort/pagination work correctly on the newly-migrated pages, since that phase adds real functionality (not just visual changes) to the admin pages.

## Out of scope (explicitly, for this pass)

- Role-based dashboards (Owner/Manager/Operator/Staff) — deferred to last, per explicit user direction; needs the role/permission system (already built) but is its own design pass once this foundation exists.
- Any new MES module (job/production orders, machine monitoring, staff attendance, maintenance scheduling) or the shop-floor tablet operator UI — none of these have a data model yet; designing their UI now would be speculative. Each needs its own brainstorming pass when the business is ready to build it.
- Navigation/information-architecture redesign — the current sidebar structure is left as-is functionally; only its component-level markup (icons, badge usage if any) is touched where this spec's components apply.
- Multi-tenancy — separately deferred, unrelated to this spec.
- Dark mode activation/testing — the plumbing exists in Velzon already; this spec doesn't audit or fix dark-mode-specific issues, since the app doesn't appear to expose the toggle as a primary supported mode today (out of scope to avoid speculative work on an untested surface).
- Server-side validation gaps (e.g. Product create has none) — noted as a contributing cause of the missing-error-UI gap, but fixing validation itself is a backend/business-logic change outside a UI/UX spec's scope. Flagged here for awareness, not addressed by this spec.
