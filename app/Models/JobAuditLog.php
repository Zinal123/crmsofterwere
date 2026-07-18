<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobAuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['job_id', 'user_id', 'action', 'description', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException('JobAuditLog rows are immutable and cannot be updated.');
        });

        static::deleting(function () {
            throw new \LogicException('JobAuditLog rows are immutable and cannot be deleted.');
        });
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
