<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use Auditable;

    protected $table = 'quotation_items';

    protected $fillable = [
        'quotation_id',
        'description',
        'amount',
        'product_id',
        'quantity',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quation::class, 'quotation_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
