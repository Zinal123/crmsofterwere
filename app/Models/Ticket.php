<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'client_machine_id',
        'client_account_id',
        'problem_type_id',
        'description',
        'status',
        'job_id',
    ];

    public function clientMachine(): BelongsTo
    {
        return $this->belongsTo(ClientMachine::class);
    }

    public function clientAccount(): BelongsTo
    {
        return $this->belongsTo(ClientAccount::class);
    }

    public function problemType(): BelongsTo
    {
        return $this->belongsTo(TicketProblemType::class, 'problem_type_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(JobPhoto::class);
    }
}
