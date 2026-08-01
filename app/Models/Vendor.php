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
}
