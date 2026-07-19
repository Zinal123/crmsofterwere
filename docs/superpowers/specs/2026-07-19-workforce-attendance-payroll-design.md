# Workforce, Attendance & Payroll — Design

## Context

Phase 1 of `docs/Oracle-Machine-Tech-Build-Order.pdf` (an external code audit of this CRM) calls
out a Worker/Employee Master and Daily Attendance as foundational, Phase-1-priority gaps — no HR
record of any kind exists in the app today, and "Worker" currently only means a login role tied
to the Job Tracking module. During brainstorming, the user confirmed the real operational need
goes further: local/daily-wage workers are routinely paid in installments through the month
rather than one lump sum at month-end, so a correct payroll calculation (not just an attendance
log) is needed now — pulling forward the core of the audit's Phase 2 "Salary & Payroll
Processing" item rather than deferring it.

This is a new, self-contained domain — no existing table, route, or view is touched except the
additive ones explicitly listed below.

## Confirmed Decisions

- **Employees are a separate HR roster, not tied to login accounts.** Only the Owner/a Manager
  marks attendance and manages records — workers themselves never log in for this. An employee
  *may* optionally be linked to an existing `User` account (for the subset who also need Job
  Tracking access), but most won't have one.
- **Pay types: monthly salary and daily wage only.** No per-piece rate — there's no production-
  count feature to multiply it against yet, so it would sit unused.
- **Full payroll calculation, not just a running estimate.** Per the user's explicit choice.
- **Leave/pay rule:** Present = full day rate. Half-day = 50% of day rate. Leave and Absent = ₹0
  (unpaid). Monthly-salary workers use the same per-day formula via a derived day rate
  (`monthly_salary ÷ days in that calendar month`) — so one formula covers both pay types.
- **Overtime:** a fixed `overtime_rate_per_hour` set per employee; payroll adds
  `overtime_hours × rate` on top of the base earned amount.
- **Deductions:** only the sum of mid-month `salary_payments` already made this month. No
  statutory deductions (PF/ESI/professional tax) — explicitly out of scope.
- **ID documents:** multiple documents per employee (Aadhar, PAN, driving license, etc.), each
  with an optional document number and an uploaded file (image or PDF).

## Approach

New domain, same architecture as every other domain in this app: Controller → Service →
Repository, `TenantScope` piped through repository reads, migrations for every new table, tests
written against real HTTP requests. Reuses the existing `App\Support\ImageCompressor` for image
ID documents (same compression this app already uses for Job Tracking photos); PDF uploads are
stored as-is (GD can't compress a PDF). New permissions follow the existing dynamic-role pattern
— Owner gets everything by default; a "Manager" role (already creatable via the existing admin
UI from the role-matrix feature) can be granted a subset.

## Database Schema

### `employees`

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | |
| phone | string | |
| email | string, nullable | |
| address | text, nullable | |
| emergency_contact_name | string, nullable | |
| emergency_contact_phone | string, nullable | |
| department | string, nullable | |
| designation | string, nullable | |
| joining_date | date | |
| pay_type | enum: monthly, daily | |
| pay_rate | decimal(10,2) | monthly salary amount, or daily wage amount, per `pay_type` |
| overtime_rate_per_hour | decimal(10,2), default 0 | |
| bank_account_holder_name | string, nullable | |
| bank_account_number | string, nullable | |
| bank_ifsc | string, nullable | |
| bank_name | string, nullable | |
| user_id | FK → users, nullable | optional link to an existing login account |
| is_active | boolean, default true | deactivate on exit; history (attendance, payments) stays intact |
| timestamps | | |

### `attendances`

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| employee_id | FK → employees | |
| date | date | |
| status | enum: present, absent, half_day, leave | |
| overtime_hours | decimal(5,2), default 0 | |
| marked_by | FK → users | the Owner/Manager who marked it |
| timestamps | | |

**Unique constraint** on `(employee_id, date)` — one attendance row per employee per day; marking
again for the same day updates the existing row rather than duplicating.

### `salary_payments`

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| employee_id | FK → employees | |
| date | date | |
| amount | decimal(10,2) | |
| note | string, nullable | |
| paid_by | FK → users | |
| timestamps | | |

### `employee_documents`

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| employee_id | FK → employees | |
| document_type | enum: aadhar, pan, driving_license, voter_id, other | |
| document_number | string, nullable | |
| path | string | compressed if image (via `ImageCompressor`), stored as-is if PDF |
| uploaded_by | FK → users | |
| timestamps | | |

## Payroll Calculation

`PayrollService::calculateMonthlyEarnings(Employee $employee, int $year, int $month): array`

1. Determine the employee's day rate: `pay_rate` directly if `pay_type = daily`; otherwise
   `pay_rate ÷ days_in_month($year, $month)`.
2. For every `attendances` row for that employee in that month: `present` contributes 1× day
   rate, `half_day` contributes 0.5× day rate, `leave`/`absent` contribute ₹0.
3. Overtime earned = `SUM(overtime_hours) × employee.overtime_rate_per_hour` across the month's
   attendance rows.
4. `total_earned = base_earned + overtime_earned`.
5. `total_paid = SUM(salary_payments.amount)` for that employee in that month.
6. `balance_due = total_earned - total_paid`.

Returns a breakdown (days present/half/leave/absent, base earned, overtime hours + amount, total
earned, total paid, balance due) — this is what both the employee's payroll view and any future
salary-slip feature would consume.

## Permissions

Added to `RolesAndPermissionsSeeder::PERMISSIONS`: `employees.view`, `employees.manage`,
`attendance.view`, `attendance.manage`, `payroll.view`, `payroll.manage-payments`. Owner gets all
six (matches the existing pattern). Worker gets none — this module is explicitly Owner/Manager-
only, per the user's confirmed requirement that only the Owner or a project manager marks
attendance.

## UI

- **Employees list + create/edit form** — basic details, pay structure, bank details, ID document
  upload/management, deactivate action (soft, via `is_active`).
- **Bulk attendance entry** — one page, date picker (defaults to today), every active employee
  listed with a quick status selector (Present/Absent/Half-day/Leave) and an overtime-hours
  field, submitted as a single batch — "the whole floor marked in a minute," matching the doc's
  own framing.
- **Monthly attendance register** — per employee, a calendar/table view of that month's marked
  days.
- **Employee payroll view** — the month's earnings breakdown, the payment ledger with a quick
  "add payment" form, and the running balance due.

## Out of Scope (this pass)

- PDF salary slips (a natural fast-follow once this is live, not built now).
- Paid-leave quotas/policy — all leave is unpaid, no limit tracking.
- Statutory deductions (PF/ESI/professional tax, TDS).
- Any change to existing modules — this is fully additive.
