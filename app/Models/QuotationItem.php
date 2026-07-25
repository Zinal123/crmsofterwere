<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $table = 'quotation_items';

    protected $fillable = [
        'quotation_id',
        'description',
        'amount',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quation::class, 'quotation_id');
    }
}
