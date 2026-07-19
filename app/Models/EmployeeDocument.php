<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = ['employee_id', 'document_type', 'document_number', 'path', 'uploaded_by'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
