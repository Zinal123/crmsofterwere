<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;

class ChartAccount extends Model
{
    use Auditable;

    protected $auditStatusFields = ['is_active'];

    /** The natural balance side for each account type (used by the Trial Balance). */
    public const DEBIT_TYPES = ['asset', 'expense'];

    public const CREDIT_TYPES = ['liability', 'equity', 'income'];

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function normalBalanceSide(): string
    {
        return in_array($this->type, self::DEBIT_TYPES, true) ? 'debit' : 'credit';
    }
}
