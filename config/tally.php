<?php

return [
    // The CRM's own business details, embedded into every Sales voucher
    // payload sent to Tally - static, not per-invoice data.
    'company' => [
        'name' => env('TALLY_COMPANY_NAME'),
        'gstin' => env('TALLY_COMPANY_GSTIN'),
        'state' => env('TALLY_COMPANY_STATE', 'Gujarat'),
    ],

    // Tally ledger names these vouchers post against - must already exist
    // in Tally exactly as named here (see the "fail, don't auto-create"
    // decision in docs/superpowers/specs/2026-09-23-tally-direct-connection-design.md).
    'ledgers' => [
        'sales' => env('TALLY_SALES_LEDGER', 'Sales Account'),
        'cgst' => env('TALLY_CGST_LEDGER', 'CGST'),
        'sgst' => env('TALLY_SGST_LEDGER', 'SGST'),
        'igst' => env('TALLY_IGST_LEDGER', 'IGST'),
    ],
];
