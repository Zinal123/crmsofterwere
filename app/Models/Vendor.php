<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory, Auditable;

    protected $auditStatusFields = ['is_active'];

    public const CATEGORIES = [
        'Laser Source',
        'Motion System',
        'Controller',
        'Accessories',
        'Raw Material',
        'Software',
        'Other',
    ];

    protected $fillable = [
        'name',
        'category',
        'gstin',
        'contact_name',
        'phone',
        'email',
        'state',
        'country',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function bills()
    {
        return $this->hasMany(VendorBill::class);
    }

    public function payments()
    {
        return $this->hasMany(VendorPayment::class);
    }

    public function totalBilled(): float
    {
        return (float) $this->bills()->sum('amount');
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function outstandingBalance(): float
    {
        return round($this->totalBilled() - $this->totalPaid(), 2);
    }
}
