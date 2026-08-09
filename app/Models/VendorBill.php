<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorBill extends Model
{
    use Auditable;

    protected $fillable = [
        'vendor_id',
        'bill_number',
        'amount',
        'date',
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

    public function payments(): HasMany
    {
        return $this->hasMany(VendorPayment::class);
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function balance(): float
    {
        return round((float) $this->amount - $this->paidAmount(), 2);
    }
}
