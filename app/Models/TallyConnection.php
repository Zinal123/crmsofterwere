<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TallyConnection extends Model
{
    protected $table = 'tally_connections';

    protected $fillable = [
        'host',
        'port',
        'company_name',
        'username',
        'password',
        'status',
        'last_checked_at',
        'last_error',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'last_checked_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
    ];
}
