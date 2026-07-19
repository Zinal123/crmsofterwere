<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'address', 'emergency_contact_name', 'emergency_contact_phone',
        'department', 'designation', 'joining_date', 'pay_type', 'pay_rate', 'overtime_rate_per_hour',
        'bank_account_holder_name', 'bank_account_number', 'bank_ifsc', 'bank_name', 'user_id', 'is_active',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'pay_rate' => 'decimal:2',
        'overtime_rate_per_hour' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
