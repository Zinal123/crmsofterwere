<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTransaction extends Model
{
    use Auditable;

    /** party_type / linked_type values -> the real model each resolves to. */
    private const PARTY_MODELS = [
        'employee' => Employee::class,
        'vendor' => Vendor::class,
        'client_account' => ClientAccount::class,
    ];

    private const LINKED_MODELS = [
        'salary_payment' => SalaryPayment::class,
        'vendor_payment' => VendorPayment::class,
    ];

    protected $fillable = [
        'type',
        'expense_category_id',
        'party_type',
        'party_id',
        'amount',
        'date',
        'payment_mode',
        'description',
        'receipt_photo',
        'linked_type',
        'linked_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function party(): ?Model
    {
        if ($this->party_type === null || $this->party_id === null) {
            return null;
        }

        $modelClass = self::PARTY_MODELS[$this->party_type] ?? null;

        return $modelClass ? $modelClass::find($this->party_id) : null;
    }

    public function linked(): ?Model
    {
        if ($this->linked_type === null || $this->linked_id === null) {
            return null;
        }

        $modelClass = self::LINKED_MODELS[$this->linked_type] ?? null;

        return $modelClass ? $modelClass::find($this->linked_id) : null;
    }
}
