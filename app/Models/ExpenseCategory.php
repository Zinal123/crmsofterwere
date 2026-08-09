<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory, Auditable;

    protected $auditStatusFields = ['is_active'];

    protected $fillable = [
        'name',
        'type',
        'party_model',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
