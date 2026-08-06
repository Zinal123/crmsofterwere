<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;

class JobPhoto extends Model
{
    use Auditable;

    protected $fillable = [
        'job_id', 'uploaded_by', 'path', 'content_hash', 'stage', 'latitude', 'longitude',
        'location_captured', 'location_flagged', 'distance_from_machine_meters',
        'map_link', 'address', 'captured_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'location_captured' => 'boolean',
        'location_flagged' => 'boolean',
        'distance_from_machine_meters' => 'float',
        'captured_at' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
