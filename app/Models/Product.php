<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Product extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

   
    protected $table = 'product';
    protected $fillable = [
        'name',
        'rate',
        'unit',
        'make',
        'active'

    ];


    
}
