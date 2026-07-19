<?php

namespace App\Support;

/**
 * The canonical list of India's 28 states + 8 union territories, used to
 * populate the billing/shipping state dropdowns on invoice creation and to
 * validate submitted state values server-side. HOME_STATE is the single
 * source of truth for "is this an intra-state (SGST+CGST) or inter-state
 * (IGST) sale?" in InvoiceService::getInvoiceDetails().
 */
class IndianStates
{
    public const HOME_STATE = 'Gujarat';

    public const LIST = [
        'Andhra Pradesh',
        'Arunachal Pradesh',
        'Assam',
        'Bihar',
        'Chhattisgarh',
        'Goa',
        'Gujarat',
        'Haryana',
        'Himachal Pradesh',
        'Jharkhand',
        'Karnataka',
        'Kerala',
        'Madhya Pradesh',
        'Maharashtra',
        'Manipur',
        'Meghalaya',
        'Mizoram',
        'Nagaland',
        'Odisha',
        'Punjab',
        'Rajasthan',
        'Sikkim',
        'Tamil Nadu',
        'Telangana',
        'Tripura',
        'Uttar Pradesh',
        'Uttarakhand',
        'West Bengal',
        'Andaman and Nicobar Islands',
        'Chandigarh',
        'Dadra and Nagar Haveli and Daman and Diu',
        'Delhi',
        'Jammu and Kashmir',
        'Ladakh',
        'Lakshadweep',
        'Puducherry',
    ];
}
