<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * Encode/decode payload manually instead of the plain 'array' cast:
     * json_encode() drops the ".0" from whole-number floats (e.g. sgst_amount
     * 4500.0 becomes "4500"), so a plain round-trip silently turns those
     * amounts into ints. JSON_PRESERVE_ZERO_FRACTION keeps them as floats.
     */
    protected function payload(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : json_decode($value, true),
            set: fn (?array $value) => $value === null ? null : json_encode($value, JSON_PRESERVE_ZERO_FRACTION),
        );
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }
}
