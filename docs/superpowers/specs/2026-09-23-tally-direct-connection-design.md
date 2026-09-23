# Tally Integration — Direct Connection (Revised) — Design Spec

**Date:** 2026-09-23
**Status:** Approved, ready for implementation planning
**Supersedes:** `docs/superpowers/specs/2026-09-22-tally-integration-design.md`'s
architecture section (local sync agent). That spec's goals, non-goals, and
rollout order still apply; only *how the CRM talks to Tally* has changed.

## Why this supersedes the prior spec

The 2026-09-22 spec assumed Tally Prime runs on a machine the CRM's cloud
VPS cannot reach over the network, so a local sync agent was designed to sit
next to Tally and poll the CRM outbound. That assumption turned out to be
wrong for this deployment: Tally Prime runs on a server that the CRM
**can** reach directly over the network. There is therefore no need for a
separate agent program — the CRM (Laravel app) can call Tally's XML gateway
directly.

The already-built CRM-side pilot (`tally_sync_queue` table/model,
`TallySyncQueueService`, the `InvoiceService` hook, and the Owner/Account
status screen) required no changes — none of it assumed an agent
specifically. Only the "how does a pending row actually reach Tally" half
(previously: an agent polls an API) changes to "the CRM posts to Tally
directly."

**Confirmed by the user (2026-09-23):**
- Tally Prime's XML gateway is reachable directly from the CRM's Hostinger
  VPS (the Tally server has a public IP/domain or a VPN link — not an
  isolated LAN-only machine).
- Tally's Security Control is enabled on that server: a username/password
  is required to access company data. There is no OAuth/redirect login
  flow — Tally has no hosted authorization server, unlike a cloud SaaS
  product. The "Connect" experience is a plain settings form, not a
  redirect.

## What's kept from the 2026-09-22 spec, unchanged

- Goals, non-goals (one-way CRM→Tally only, no bank reconciliation/GST
  filing feature), and the 4-source rollout order (Sales → Purchase →
  Payment/Receipt → Payroll) — see that spec for the full rationale.
- `tally_sync_queue` table, `TallySyncQueueService`, and the
  `InvoiceService::createInvoiceWithDetails()` hook — already built
  (commits `ba67dbe`..`25899eb`), correct as-is, no changes here.
- The Owner/Account "Tally Sync" status screen (already built) — kept, with
  one small change to its staleness warning (see Error handling below).

## What's replaced

The prior spec's "Architecture," "Security," and agent-related parts of
"Rollout" are replaced by this section. Everything else in that spec still
stands.

### Already-built, now-unused code

Task 4 (Sanctum `tally-agent` ability + `tally:create-agent-token` command)
and Task 5 (`GET /api/tally-sync/pending`, `POST
/api/tally-sync/{id}/acknowledge`) were built for the agent-polls-us model.
**Decision: keep this code in place, unused, rather than deleting it** — it
isn't wired into anything new and costs nothing to leave. If a future Tally
deployment ever *does* need an agent (e.g. a different customer's Tally
runs somewhere unreachable), this code is already there.

## Architecture

```
Invoice created
  -> TallySyncQueueService::enqueueSalesInvoice()   [unchanged, already built]
  -> tally_sync_queue row (status: pending)
  -> TallyPostingService::attempt($queue)           [new]
       -> loads the one `tally_connections` row
       -> if not connected: leave pending, return (no error - just not set up yet)
       -> TallyVoucherXmlBuilder builds the Sales-voucher XML            [new]
       -> TallyClient posts it to the configured Tally server            [new]
       -> parses Tally's XML response -> updates queue row (synced/failed)

Scheduled sweep (artisan command, runs every few minutes via existing cron)
  -> re-runs TallyPostingService::attempt() for every pending/under-cap-failed row
```

No agent, no polling API, no localhost-only restriction on Tally's side —
the CRM calls out to the configured host/port directly over HTTPS/HTTP (per
however Tally's gateway is actually exposed on that server; confirmed
during implementation).

## Data model

### New: `tally_connections`

Single-row table (one Tally company per CRM instance, matching the current
setup):

| Column | Notes |
|---|---|
| `id` | |
| `host` | Tally server hostname/IP |
| `port` | Tally XML gateway port |
| `company_name` | Exact Tally company name to target |
| `username` | Tally Security Control username |
| `password` | Laravel `encrypted` cast — encrypted at rest, decrypted transparently on read, never appears in logs/queue payloads |
| `status` | `disconnected` / `connected` / `failed` |
| `last_checked_at` | nullable timestamp |
| `last_error` | nullable text — Tally's own rejection message or a network error, shown verbatim on the settings screen |
| `timestamps` | |

### Unchanged: `tally_sync_queue`

Already built (Task 1). No schema changes. `payload` (built by the
already-built `TallySyncQueueService`) already carries everything
`TallyVoucherXmlBuilder` needs: company/customer/line-item data with real
product names (fixed in commit `25899eb`) and the SGST/CGST/IGST split.

## Components (new)

- **`TallyConnectionService`** — `testConnection(TallyConnection $connection): void`. Builds a minimal "list companies" style XML request via `TallyClient` using the given host/port/company/username/password, with a 5s timeout. Updates the connection row's `status`/`last_checked_at`/`last_error` based on the outcome (success / auth rejected / unreachable).
- **`TallyVoucherXmlBuilder`** — `buildSalesVoucher(array $payload): string`. Turns a `tally_sync_queue.payload` array into Tally's Sales-voucher XML, including one inventory entry per line item (full per-product detail, matched by product name against a Tally Stock Item — per the user's explicit choice to track inventory in Tally, not just financial totals) plus the party/Sales/CGST/SGST/IGST ledger entries. Exact tag names/schema are worked out against the real Tally server during implementation (same posture as the original spec — this can't be finalized from documentation alone).
- **`TallyClient`** — thin HTTP wrapper: `post(TallyConnection $connection, string $xml, int $timeoutSeconds): TallyResponse`. Owns the actual HTTP call and timeout (caller-supplied: ~5s for `TallyConnectionService`'s lightweight connectivity check, ~15s for `TallyPostingService`'s heavier voucher post, since Tally has more work to do accepting a full voucher than listing companies); parses Tally's XML response into a simple success/error value object (`accepted: bool`, `tallyVoucherId: ?string`, `errorMessage: ?string`).
- **`TallyPostingService`** — `attempt(TallySyncQueue $queue): void`. Orchestrates the above three for one queue row; the only place that touches `tally_sync_queue` status transitions for real Tally attempts. Called (a) inline right after invoice creation for near-real-time delivery, and (b) by the scheduled sweep command for anything still pending/under the retry cap.
- **New artisan command `tally:sync-pending`** — registered on Laravel's scheduler (every few minutes, via the existing cron — no new server infrastructure), calls `TallyPostingService::attempt()` for every `pending` row and every `failed` row with `attempts < 5`. Past that cap, a row stays `failed` until a human retries it from the status screen (avoids infinite retry storms on a permanently broken row, e.g. a ledger that will never exist without manual setup).

## Master data handling

**No auto-creation of any kind** (customer ledgers, tax ledgers, Sales
ledger, or Stock Items) — explicit user decision. If a required Tally
master (a ledger or a stock item) doesn't exist, Tally's own XML response
rejects the voucher; `TallyPostingService` records that rejection message
verbatim in `last_error` and marks the row `failed`. A human creates the
missing master in Tally, then retries from the status screen. This applies
uniformly to every master-data type this integration touches — no special
case for customers vs. products vs. tax ledgers.

This means there's no ODBC-based pre-check step (the prior spec's "before
posting a voucher, the agent checks via ODBC" is dropped) — the CRM simply
attempts the post and trusts Tally's own validation, which is simpler and
sufficient given the "fail, don't auto-create" policy.

## Settings screen ("Connect" flow)

New Owner/Account screen (reusing the existing `tally-sync.view`
permission — same audience as the Tally Sync status screen, no new
permission needed) at `GET /tally-connection`:

- A form: host, port, company name, username, password. Submitting
  upserts the single `tally_connections` row (password re-encrypted via
  the `encrypted` cast).
- A "Connect" button triggers `TallyConnectionService::testConnection()`
  synchronously (it's one quick request, no background job needed) and
  redirects back showing the resulting status:
  - **Connected** — green badge, `last_checked_at` shown.
  - **Auth rejected** — red badge, Tally's own rejection message shown
    verbatim.
  - **Unreachable/timeout** — red badge, a clear "could not reach Tally
    server" message.

No OAuth redirect anywhere in this flow — confirmed Tally has no such
capability.

## Error handling & visibility

- The existing "Tally Sync" status screen (Task 6, unchanged structurally)
  keeps showing `reference_no`/`voucher_type`/`status`/`attempts`/
  `last_error`/retry — those now reflect real Tally responses.
- Its "agent hasn't checked in" staleness warning is replaced with a
  "Tally not connected" warning, driven by the new `tally_connections.status`
  column instead of the now-irrelevant `tally_agent_last_checkin` cache key.
- The new Tally Connection settings screen is the other visibility surface,
  for connection-level (not per-voucher) problems.
- A failed voucher post never breaks invoice creation — `TallyPostingService::attempt()`
  is wrapped in try/catch inline; worst case a row stays `pending` for the
  next scheduled sweep.

## Testing plan

- **Automated, SQLite, no real Tally needed for most of it:**
  - `TallyConnectionService` / `TallyClient`: `Http::fake()` covering
    success / auth-rejected / timeout responses, asserting the connection
    row's resulting status/error.
  - `TallyVoucherXmlBuilder`: assert the generated XML's structure/values
    against a known invoice payload fixture (validates "did we build the
    XML we intended," not "does Tally accept it").
  - `TallyPostingService`: `Http::fake()` covering synced / rejected /
    network-error outcomes, asserting `tally_sync_queue` status
    transitions and the attempt-cap behavior.
  - `tally:sync-pending` command: asserts which rows it touches/skips
    (respecting the cap, skipping `synced` rows).
  - Settings screen: permission gate, form validation, password never
    appearing in the rendered page or any log.
- **Real-Tally verification (manual, once per rollout stage, same posture
  as the original spec):** the exact XML tags Tally's Security-Control
  login and Sales-voucher import expect can only be confirmed by round-
  tripping against the real server. This happens during implementation of
  `TallyVoucherXmlBuilder`/`TallyClient`, not upfront.

## Open questions for implementation planning

- Exact Tally XML schema for Security-Control authentication and the
  Sales-voucher envelope — confirmed against the real server during
  implementation.
- Exact scheduled-sweep interval (every few minutes is the starting
  assumption) — tuned once real network/Tally response latency is known.
- Whether `tally_connections` needs to support more than one row in the
  future (multiple Tally companies) — out of scope now; today's setup is
  one CRM instance, one Tally company.
