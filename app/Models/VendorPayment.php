<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPayment extends Model
{
    use Auditable;

    protected $fillable = [
        'vendor_id',
        'vendor_bill_id',
        'amount',
        'date',
        'payment_mode',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class, 'vendor_bill_id');
    }
}
