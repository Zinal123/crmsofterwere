<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePartRequest extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'client_machine_id',
        'client_account_id',
        'product_id',
        'quantity',
        'note',
        'status',
    ];

    public function clientMachine(): BelongsTo
    {
        return $this->belongsTo(ClientMachine::class);
    }

    public function clientAccount(): BelongsTo
    {
        return $this->belongsTo(ClientAccount::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
