<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPhoto extends Model
{
    protected $fillable = [
        'job_id', 'uploaded_by', 'path', 'latitude', 'longitude',
        'location_captured', 'map_link', 'address', 'captured_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'location_captured' => 'boolean',
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
