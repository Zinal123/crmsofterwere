<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'machine_id', 'site_name', 'created_by', 'assigned_to',
        'priority', 'due_date', 'status', 'decided_by', 'decided_at', 'rejection_reason',
        'on_hold_reason', 'completion_notes', 'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'decided_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function photos()
    {
        return $this->hasMany(JobPhoto::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(JobAuditLog::class);
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }
}
