<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invetry extends Model
{
    use HasFactory, Auditable;
    protected $table  ="invetry";
    protected $fillable = [
        'product_id',
        'quantity',
        'vandername',
        'rate',

    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
