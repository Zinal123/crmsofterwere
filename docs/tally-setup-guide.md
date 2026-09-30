# Connect the CRM to Tally - Setup Guide

Every invoice you create in the CRM is sent to Tally automatically as a **Sales voucher**.
Sync is one-way: CRM to Tally. Only Sales invoices are supported today.

## Before you start

- Tally Prime is running with your company open.
- You are logged in to the CRM as **Owner** or **Account**.
- The CRM must be able to reach the PC where Tally runs:

| Where the CRM runs | Tally host to use |
|---|---|
| Same PC as Tally | `localhost` |
| Another PC on the same office network | That PC's IP, e.g. `192.168.1.20` |
| Online server (e.g. Hostinger) | Not reachable by default. Ask your developer to set up a VPN or a secured port forward to the Tally PC. |

## Step 1 - Turn on Tally's server (once)

1. In Tally: `F1 (Help)` > `Settings` > `Connectivity` > `Client/Server configuration`.
2. Set **TallyPrime acts as** = `Server` (or `Both`).
3. Set **Port** = `9000`.
4. Check it works: open `http://localhost:9000` in a browser on the Tally PC.
   You should see "TallyPrime Server is Running".

## Step 2 - Create the masters in Tally

Names must match the CRM **exactly** (spelling, spaces, capitals).

| In Tally | Name | Group / details |
|---|---|---|
| Ledger | `Sales Account` | Sales Accounts |
| Ledger | `CGST` | Duties & Taxes, GST, Central Tax |
| Ledger | `SGST` | Duties & Taxes, GST, State Tax |
| Ledger | `IGST` | Duties & Taxes, GST, Integrated Tax |
| Ledger | one per customer, same name as in the CRM | Sundry Debtors |
| Unit | `Nos` (or whatever unit the CRM uses) | Simple unit |
| Stock Item | one per CRM product, same name as in the CRM | Units = the unit above |

Whenever you add a new customer or product in the CRM, add it in Tally before invoicing.

## Step 3 - Connect the CRM

1. Open **Tally Connection** in the CRM.
2. Fill in:
   - **Host**: from the table above
   - **Port**: `9000`
   - **Company name**: exactly as shown in Tally
   - **Username / Password**: your Tally security login. If Tally has no security control, enter any text.
3. Click **Save**, then **Connect**.
4. You should see **"Connected to Tally successfully."**

Your administrator also sets these in the CRM's `.env` file:

```
TALLY_COMPANY_NAME="Your Company Name"
TALLY_COMPANY_GSTIN=your GSTIN
TALLY_COMPANY_STATE=Gujarat
```

## Step 4 - Test with one invoice

1. Create an invoice in the CRM.
2. Open **Tally Sync**. The invoice should show **synced**.
3. In Tally open `Day Book`. The voucher is there with Reference `SALES-<number>`.
   Check the party, sales amount and GST split.

Tax split: customers in your home state get CGST + SGST; other states get IGST.

## Tally Sync statuses

| Status | Meaning | What to do |
|---|---|---|
| synced | In Tally | Nothing |
| pending | Not sent yet, e.g. Tally was off or not connected | It retries automatically. Make sure Tally is open and Connect shows connected. |
| failed | Tally rejected it. The message says why. | Fix the cause in Tally, then press **Retry** on that row. |

## Common errors

| Message | Fix |
|---|---|
| `Ledger '...' does not exist!` | Create that customer/sales/tax ledger in Tally with the exact name. |
| `Stock Item '...' does not exist!` | Create the stock item (and unit) in Tally with the exact name. |
| `Voucher date is missing` | Free/Educational Tally only accepts voucher dates on the **1st, 2nd or 31st** of a month. Use a licensed Tally or one of those dates. |
| `The date ... is Out of Range!` | The date is outside the company's financial year in Tally. |
| `Could not reach Tally server` | Tally is closed, the server setting is off, the port is wrong or a firewall blocks it. |
| `Tally did not return company ...` | The company is not open in Tally, or the name doesn't match. |

## Automatic retries (administrator)

Schedule this command to run every 5 minutes so pending invoices are re-sent when Tally comes back:

```
php artisan tally:sync-pending
```

## Notes

- The voucher number in Tally is assigned by Tally. The CRM invoice number is linked through the Reference field.
- Invoices are never duplicated: re-sending the same invoice updates the existing voucher.
- Purchase, receipt and payroll vouchers are not synced yet.
