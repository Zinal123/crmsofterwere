<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TallySyncQueue extends Model
{
    protected $table = 'tally_sync_queue';

    protected $fillable = [
        'source_type',
        'source_id',
        'voucher_type',
        'reference_no',
        'payload',
        'status',
        'tally_voucher_id',
        'attempts',
        'last_error',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }
}
