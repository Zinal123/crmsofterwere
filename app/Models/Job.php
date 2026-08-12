<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    /** Statuses that are terminal — never counted as overdue regardless of due_date. */
    private const TERMINAL_STATUSES = ['completed', 'rejected'];

    protected $fillable = [
        'title', 'description', 'machine_id', 'site_name', 'created_by', 'assigned_to',
        'priority', 'due_date', 'status', 'decided_by', 'decided_at', 'rejection_reason',
        'on_hold_reason', 'completion_notes', 'completed_at', 'overdue_flagged_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'decided_at' => 'datetime',
        'completed_at' => 'datetime',
        'overdue_flagged_at' => 'datetime',
    ];

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereNotIn('status', self::TERMINAL_STATUSES);
    }

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

    public function checklistItems()
    {
        return $this->hasMany(JobChecklistItem::class)->orderBy('id');
    }

    public function auditLogs()
    {
        return $this->hasMany(JobAuditLog::class);
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }

    public function materials(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JobMaterial::class);
    }
}
