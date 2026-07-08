<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Cnsthinks extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

   
    protected $table = 'cnsthinks';
    protected $fillable = [
        'product_id',
        'cuttingthinks',

    ];


    
}
