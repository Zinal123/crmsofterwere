# Tally Integration (Local Sync Agent) — Design Spec

**Date:** 2026-09-22
**Status:** Approved, ready for implementation planning
**Supersedes for now:** `docs/superpowers/specs/2026-09-22-double-entry-ledger-design.md`
(shelved, not deleted — see Background)

## Background

The user initially asked for "a Tally accounting tool" for the Oracle
Machine Tech CRM. That was first scoped as building Tally-equivalent
double-entry bookkeeping *inside* the CRM (see the shelved ledger spec
above). On further discussion, the actual need is different: the
accountant already uses **real Tally Prime** (paid, licensed), and the goal
is to stop manually re-entering CRM transactions into it — i.e. genuine
integration with the existing Tally installation, not a parallel
accounting system.

This spec supersedes that direction for now. The ledger spec is kept in
the repo in case an internal ledger is wanted later independent of this
integration; it is not part of this project.

## Goals

- Automatically push CRM financial activity (sales invoices, vendor
  bills/purchases, payroll, daily expenses) into the accountant's real
  Tally Prime installation as proper vouchers — no manual re-entry.
- Near-real-time: a transaction recorded in the CRM should appear in Tally
  within seconds under normal conditions.
- Safe by construction: never expose Tally's XML gateway to the internet;
  no inbound connection to the Tally machine, ever.
- Visibility: Owner/Account can see sync status and failures without
  needing to inspect logs or ask the accountant.

## Non-goals (explicitly out of scope)

- Pulling data *from* Tally back into the CRM (two-way sync) — one-way
  CRM → Tally only, per explicit decision. Anything entered directly in
  Tally (corrections, adjustments) stays authoritative in Tally only.
- Building an internal double-entry ledger in the CRM (see shelved spec).
- Bank reconciliation, GST return filing — Tally already does both once
  it has the correct voucher data; no separate CRM feature needed for
  these once this integration exists.
- Automating installation of the sync agent itself onto the accountant's
  machine (a person installs/runs it once; this spec covers what it does
  once running, not a zero-touch deployment pipeline).

## Prerequisite (blocks implementation, not design)

**Where Tally Prime actually runs is not yet known** — accountant's own
PC/laptop vs. a shared office server. This must be confirmed before
implementation starts, because it determines which machine the sync agent
gets installed on. It does not change the architecture below, which works
identically either way (the agent runs wherever Tally runs).

## Architecture

Tally Prime's only integration surface is an **XML-over-HTTP gateway**
(Tally listens locally, default port 9000, only while Tally is open) plus
an ODBC driver for read queries. It has no authentication of its own and
is not reachable from the internet by default — and it should stay that
way.

The CRM is cloud-hosted (Hostinger VPS) and cannot reach into the
accountant's local network. So instead of the CRM calling Tally directly,
a **local sync agent** runs on/near the Tally machine and does the work,
making only **outbound** HTTPS calls to the CRM's API — no inbound port is
ever opened on the Tally side.

**Flow:**
1. CRM records an invoice / vendor bill / salary payment / daily
   expense-or-receipt (existing write paths, each gets one additional
   hook appended — same additive pattern used elsewhere in this app).
2. That hook inserts a row into a new `tally_sync_queue` table instead of
   posting a journal entry.
3. The agent polls the CRM's API for pending queue rows (short interval,
   for near-real-time delivery).
4. For each row, the agent builds the Tally XML voucher and posts it to
   the local Tally gateway (`localhost:9000` from the agent's point of
   view).
5. The agent reports success/failure back to the CRM API. The CRM updates
   the queue row's status and surfaces failures on a "Tally Sync" status
   screen (Owner/Account role).

## Data model

**`tally_sync_queue`**
- `id`
- `source_type`, `source_id` — Invoice / VendorBill / SalaryPayment /
  DailyTransaction and the originating record's id
- `voucher_type` — Tally's own voucher type name (Sales / Purchase /
  Payment / Receipt)
- `payload` — serialized data the agent needs to build the XML
- `status` — `pending` / `synced` / `failed`
- `tally_voucher_id` — Tally's own identifier once confirmed created,
  used for idempotency checks
- `reference_no` — unique per queue row, embedded in the Tally voucher's
  reference field so a retried sync can detect "Tally already has this"
  before creating a duplicate
- `attempts`, `last_error`
- `timestamps`

## Voucher mapping & master-data handling

- Each of the 4 source types maps to a Tally voucher type per the table
  in the Rollout section below.
- **Master data:** Tally requires the Customer/Vendor/Employee to exist
  as a Ledger master before a voucher referencing it can post. Before
  posting a voucher, the agent checks (via ODBC query) whether the
  relevant ledger exists in Tally, and auto-creates it from the CRM's
  customer/vendor/employee record if missing.
- **Idempotency:** the `reference_no` on each queue row is embedded in
  the voucher's narration/reference field in Tally. Before creating a
  voucher, the agent queries Tally for an existing voucher with that
  reference, preventing duplicate posts on retry after a network blip.
- **GST/HSN alignment:** invoice GST/HSN data (already correct per the
  existing Pan-India GST Engine) maps directly onto Tally's GST voucher
  fields. This requires Tally's own company GST/state configuration to
  already match the CRM's — a one-time setup check, not a per-transaction
  concern.

## Rollout order

Building and validating all four voucher mappings against a real Tally
instance simultaneously is high-risk. Build and validate in this order,
each proven against the accountant's actual Tally before starting the
next:

1. **Sales invoices** → Tally Sales voucher. Highest volume, simplest
   structure — proves the whole pipeline end to end (queue → agent → XML
   → Tally → status feedback).
2. **Vendor bills/purchases** → Tally Purchase voucher.
3. **Daily expenses/receipts** → Tally Payment/Receipt vouchers.
4. **Payroll** → last, and possibly its own follow-up design pass. Tally
   has a separate Payroll module with its own Employee/Pay Head master
   data model, meaningfully more complex than the other three, which are
   generic vouchers.

## Error handling & visibility

- A failed queue row (Tally unreachable, ledger conflict, XML rejected by
  Tally) stays `failed` with `last_error` recorded; retries with backoff
  up to a capped attempt count.
- "Tally Sync" status screen (Owner/Account role): lists pending/failed
  rows with the failure reason and a manual retry action.
- If the agent hasn't checked in within a configurable threshold (Tally
  machine off, agent not running), the status screen shows a stale
  warning rather than letting a backlog build up invisibly.

## Security

- The agent authenticates to the CRM API with its own per-installation
  token, not a user login — limits blast radius if the agent machine is
  ever compromised to sync-queue access only.
- All agent↔CRM traffic over HTTPS.
- Tally's XML gateway is only ever addressed as `localhost` by the agent
  running on the same machine — never exposed beyond that, no VPN/port-
  forward/tunnel is part of this design.

## Testing & the real challenges

This project has a fundamentally different verification posture than the
rest of this codebase:

- **Requires a real Tally Prime instance to test against** — SQLite/mocks
  can validate the CRM-side queue logic, but the actual XML acceptance by
  Tally can only be confirmed against a real (trial or licensed) Tally
  install with a test company. This replaces the usual "feature tests +
  live-MySQL smoke test" pattern with an additional manual Tally-side
  verification step per voucher type, done once per rollout stage above.
- **Operational dependency outside our control.** Unlike the CRM itself
  (fully controlled on the Hostinger VPS), this pipeline only works while
  the agent is installed, running, and Tally is open on a machine we
  don't manage. Downtime there just queues up backlog, it doesn't fail
  silently (the staleness warning above catches it), but it is a real
  dependency this project didn't have before.
- **Version drift.** A Tally Prime update could shift its XML schema,
  silently breaking a mapping until a sync failure is noticed. No
  automated way to detect this ahead of time; periodic manual
  re-verification after any Tally update is the mitigation.
- **The "where does Tally run" unknown** (see Prerequisite above) blocks
  finalizing which machine gets the agent, though it doesn't change this
  design.

## Testing plan

- Feature tests (CRM side, SQLite is fine here): each of the 4 hooks
  correctly enqueues a `tally_sync_queue` row with the right
  `voucher_type` and payload; queue status transitions; the status
  screen surfaces failed rows correctly; retry action re-queues.
- Agent-side tests (against a real Tally test company, manual/semi-
  automated, run once per rollout stage): XML for each voucher type is
  accepted by Tally; missing ledger master gets auto-created; duplicate
  `reference_no` is correctly detected and skipped.
- End-to-end smoke test per rollout stage: a real CRM transaction of that
  type appears correctly in the Tally test company within the expected
  latency.

## Rollout

1. Confirm the Prerequisite (where Tally runs) with the accountant.
2. Migration adds `tally_sync_queue`.
3. Build agent + Sales-invoice mapping (rollout stage 1), validate against
   a Tally test company, deploy agent to the real machine.
4. Repeat for Purchase, then Payment/Receipt, then Payroll (its own design
   pass first).

## Open questions for implementation planning

- Exact agent tech stack/packaging (e.g. a small .NET or Node service
  installable as a Windows service) — decided during implementation, does
  not affect this design.
- Polling interval for near-real-time delivery — tuned during
  implementation/testing against real network conditions.
- Exact Tally XML schema details per voucher type — worked out against
  the real Tally test company during each rollout stage, not upfront.
