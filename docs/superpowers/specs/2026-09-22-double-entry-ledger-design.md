# Double-Entry Ledger & Voucher Entry — Design Spec

**Date:** 2026-09-22
**Status:** Approved, ready for implementation planning
**Phase:** 1 of 3 in the "Tally-like accounting" initiative (see Background)

## Background

The user asked for "a Tally accounting tool" for the Oracle Machine Tech CRM.
Tally (Tally Prime/ERP) bundles several largely-independent subsystems:
double-entry bookkeeping (ledgers/vouchers), GST return filing (GSTR-1/3B),
bank reconciliation, inventory valuation, etc. That's too large for one spec,
so it was decomposed into three phases:

1. **Double-entry ledger + voucher entry** (this spec) — the foundation.
2. Bank Reconciliation — depends on Phase 1's real bank/cash ledger accounts.
3. GST Return Filing (GSTR-1/3B exports) — mostly reuses existing invoice/
   purchase data, benefits from Phase 1 for clean input-tax-credit tracking.

The CRM currently has a **read-only cash-basis reporting layer**
(`[[accounting_reporting_layer]]`, `App\Services\Reporting\AccountingService`,
`chart_accounts` table) that *derives* Trial Balance and P&L by re-reading
Invoice/DailyTransaction/VendorBill/SalaryPayment rows at request time — no
journal entries exist anywhere in the system today. That reporting layer was
explicitly built as "Option A — a clean upgrade path to [full double-entry]
if ever needed." This spec is that upgrade.

## Goals

- Real double-entry journal entries with enforced debit = credit balancing.
- Manual voucher entry (Payment / Receipt / Contra / Journal) for Owner and
  the Account role, matching Tally's core voucher types.
- Existing money-movement flows (invoice payment, vendor bill payment,
  payroll, daily cash-book expense/receipt) automatically post the matching
  journal entry — no double data entry, ledger always reflects reality.
- Trial Balance and P&L reports upgraded to read from real journal entries
  instead of deriving/guarding against double-counts.
- New Ledger (account statement) and Day Book reports.
- Full audit trail via the existing generic `AuditLogService` pattern.

## Non-goals (explicitly out of scope for this phase)

- Bank reconciliation (Phase 2).
- GST return filing / GSTR exports (Phase 3).
- Backfilling historical journal entries for all past data — see Opening
  Balances below; this phase is a cutover-date approach, not a full
  historical rebuild.
- Multi-currency, multi-company, inventory valuation — not requested.

## Data Model

Two new tables:

**`journal_entries`** (voucher header)
- `id`
- `voucher_type` — enum: `payment`, `receipt`, `contra`, `journal`
- `entry_date`
- `narration`
- `reference_no` (nullable, free text — e.g. cheque number)
- `created_by` (user id)
- `source_type`, `source_id` (nullable — set when auto-posted from an
  Invoice/VendorBill/SalaryPayment/DailyTransaction; null for manually
  entered vouchers)
- `timestamps`

**`journal_entry_lines`**
- `id`
- `journal_entry_id` (FK)
- `chart_account_id` (FK to the existing `chart_accounts` table)
- `debit` (decimal, default 0)
- `credit` (decimal, default 0)
- `particulars` (nullable free text per line)

The existing `chart_accounts` table is reused as-is — this phase gives it
its real purpose (posting target) instead of being reference data for
derived reports only.

**Balancing rule**, enforced in `JournalEntryService::post()`: for a given
`journal_entries` row, `SUM(journal_entry_lines.debit) == SUM(journal_entry_lines.credit)`,
and there must be at least one debit line and one credit line. Enforced
server-side (source of truth) and mirrored client-side in the voucher forms
for immediate feedback.

## Auto-posting from existing flows

Each of the four existing write paths gets one additional call appended at
the end of its existing service method — the original logic, validation,
and tests for those paths are untouched:

| Existing flow | New journal entry posted |
|---|---|
| Invoice payment recorded | Dr. Bank — Cr. Accounts Receivable |
| Vendor bill payment recorded | Dr. Accounts Payable — Cr. Cash/Bank (by `payment_mode`) |
| Payroll (SalaryPayment) recorded | Dr. Salaries & Wages Expense — Cr. Bank |
| Daily Cash Book receipt/expense recorded | Dr./Cr. Cash/Bank (by `payment_mode`) — Cr./Dr. the selected expense/income category account |

`VendorPayment` and `DailyTransaction` already record a `payment_mode`
column (`cash`/`bank`/`upi`/`cheque`); auto-posting maps `cash` to the Cash
account and `bank`/`upi`/`cheque` to the Bank account. `Invoice` payments
and `SalaryPayment` have no payment-mode field today, so both default to the
Bank account — if a specific invoice receipt or salary payout was actually
handled in cash, a manual Contra voucher moves the funds between Cash and
Bank afterward. This keeps the auto-posting hook additive (no new columns on
Invoice/SalaryPayment), consistent with not touching existing write paths
beyond appending the posting call.

These auto-posted entries carry `source_type`/`source_id` pointing back to
the originating record.

**Lock rule:** an auto-posted journal entry cannot be edited or deleted
directly from the ledger UI. The edit/delete actions are hidden for rows
where `source_type` is set, with a message directing the user to edit the
source record instead (e.g. edit the invoice payment), which re-posts the
journal entry to match. This guarantees the ledger can never silently
disagree with the record it was generated from.

Manually-created vouchers (`source_type` null) are fully editable and
deletable by Owner/Account, with every change captured in the audit log —
per the user's explicit choice over reversal-only entries.

## Voucher entry screens

New "Vouchers" section, permission-gated to Owner + Account role (matching
the existing role-dashboard setup):

- **Payment voucher** — Dr. an expense/liability account, Cr. Cash/Bank.
- **Receipt voucher** — Dr. Cash/Bank, Cr. an income/asset account.
- **Contra voucher** — transfers between Cash and Bank accounts only (both
  sides restricted to `chart_accounts` rows of a "cash/bank" account
  subtype).
- **Journal voucher** — free-form debit/credit account selection, for
  adjustments not involving cash directly (e.g. correcting entries).

Each form: entry date, one or more debit lines, one or more credit lines
(account + amount + particulars), narration, reference number. Submission
calls `JournalEntryService::post()`.

## Reports upgrade

`AccountingService` (`Trial Balance`, `P&L`) switches from deriving figures
from Invoice/DailyTransaction/VendorBill/SalaryPayment rows to computing
`SUM(debit) - SUM(credit)` grouped by `chart_account_id` from
`journal_entry_lines` within the requested date range. This removes the
"double-count guard" for linked mirror rows entirely (nothing to guard
against once there's one real ledger), and Owner's Equity becomes a real
computed balance instead of a plugged balancing figure.

Two new reports, both straightforward queries over the new tables:

- **Ledger / Account Statement** — drill into one chart account, see every
  posted line (manual + auto-posted) chronologically with a running balance.
- **Day Book** — all vouchers posted on a given date, in voucher order.

## Opening balances / cutover

A cutover date is chosen (e.g. start of the current financial year). Owner
or Account manually enters a single opening-balance Journal voucher with
each account's balance as of that date, derived from the current read-only
reports' output. Auto-posting only applies to transactions recorded from the
cutover date forward — no automated backfill of historical records.

## Error handling

- Unbalanced entry (debit total ≠ credit total) → validation error, entry
  not saved, both client- and server-side.
- Entry with only debit lines or only credit lines → validation error.
- Negative amounts rejected.
- Attempt to edit/delete an auto-posted (`source_type` set) entry directly →
  action blocked with a message pointing to the source record.
- Attempt to access voucher/ledger screens without Owner/Account role →
  403, consistent with existing permission-gated routes in this app.

## Testing

- Feature tests per voucher type: successful creation, unbalanced-entry
  rejection, single-sided rejection, permission gating (Owner/Account only).
- Tests confirming auto-posting fires correctly for each of the 4 source
  flows and produces a balanced entry with correct `source_type`/`source_id`.
- Tests confirming auto-posted entries reject direct edit/delete, with
  manually-created vouchers allowing both.
- Trial Balance test asserting total debits always equal total credits after
  a mix of manual and auto-posted entries.
- Ledger/Account Statement test asserting running balance is correct.
- Audit log coverage: create/edit/delete of journal entries all produce
  `AuditLogService` entries, following the same `TYPE_PERMISSIONS`-keyed
  pattern as existing modules (new `journal_entry` type, `accounting.view-audit`
  permission).
- Live-MySQL smoke test after merge (per this project's established
  practice — SQLite has masked real schema bugs here before, see
  `[[sqlite_vs_mysql_testing_gap]]`).

## Rollout

1. Migration adds `journal_entries` and `journal_entry_lines`.
2. Seeder ensures standard chart-of-accounts entries needed for auto-posting
   targets exist (Bank, Cash, Accounts Receivable, Accounts Payable,
   Salaries & Wages Expense, GST Payable) if not already present from the
   existing `ChartOfAccountsSeeder`.
3. Re-run `RolesAndPermissionsSeeder` to add the new voucher/ledger
   permissions (`accounting.post-voucher`, `accounting.view-audit`, etc. —
   exact names finalized during implementation planning).
4. After deploy, Owner picks the cutover date and manually posts the
   opening-balance journal entry.

## Open questions for implementation planning

- Exact permission names/keys (follow existing `module.action` convention).
- Whether Contra voucher account-subtype restriction (Cash/Bank only) needs
  a new column on `chart_accounts` (e.g. `is_cash_or_bank`) or can be
  inferred from existing `code`/`name` conventions — resolved during
  implementation, doesn't affect the design above either way.
