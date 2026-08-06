<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invetry extends Model
{
    use HasFactory, Auditable;

    /** Below this on-hand quantity, a part is considered Low Stock (Reports page and low-stock notifications). */
    public const LOW_STOCK_THRESHOLD = 5;

    protected $table  ="invetry";
    protected $fillable = [
        'product_id',
        'quantity',
        'vandername',
        'rate',
        'low_stock_notified_at',
    ];

    protected $casts = [
        'low_stock_notified_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
