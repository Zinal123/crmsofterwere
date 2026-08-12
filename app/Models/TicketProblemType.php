<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketProblemType extends Model
{
    use HasFactory, Auditable;

    protected $auditStatusFields = ['is_active'];

    protected $fillable = [
        'category',
        'name',
        'description',
        'default_priority',
        'estimated_resolution_hours',
        'checklist_template_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'estimated_resolution_hours' => 'integer',
    ];

    public function checklistTemplate()
    {
        return $this->belongsTo(\App\Models\ChecklistTemplate::class);
    }
}
