<?php

return [
    // The CRM's own business details, embedded into every Sales voucher
    // payload sent to Tally - static, not per-invoice data.
    'company' => [
        'name' => env('TALLY_COMPANY_NAME'),
        'gstin' => env('TALLY_COMPANY_GSTIN'),
        'state' => env('TALLY_COMPANY_STATE', 'Gujarat'),
    ],
];
