<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;

class JobPhoto extends Model
{
    use Auditable;

    protected $fillable = [
        'job_id', 'ticket_id', 'uploaded_by', 'path', 'content_hash', 'stage', 'latitude', 'longitude',
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

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * A ticket-submitted photo (client portal, no staff uploader/GPS) keeps
     * the 'ticket_photo' audit-trail type it always had, distinct from a
     * Job proof-of-work photo - this is one canonical table now, not one
     * canonical audit type. See app/Providers/AppServiceProvider.php's
     * morph map, which points both aliases at this same class.
     */
    public function getMorphClass()
    {
        return $this->ticket_id !== null ? 'ticket_photo' : 'job_photo';
    }
}
