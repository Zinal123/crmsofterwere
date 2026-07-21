<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use Auditable;

    protected $fillable = ['employee_id', 'date', 'status', 'overtime_hours', 'marked_by'];

    protected $casts = [
        // Explicit 'Y-m-d' format (not the bare 'date' cast) so the attribute is
        // stored and compared without a time component. The bare 'date' cast
        // formats writes as 'Y-m-d H:i:s' (via fromDateTime()), which mismatches
        // the raw 'Y-m-d' strings used as updateOrCreate() search keys against the
        // (employee_id, date) unique constraint — on sqlite this silently fails to
        // find the existing row and throws a duplicate-key error on re-insert
        // (MySQL's DATE column coerces the extra time away, masking the same bug).
        'date' => 'date:Y-m-d',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function marker()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
