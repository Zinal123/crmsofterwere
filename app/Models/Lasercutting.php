<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Lasercutting extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

   
    protected $table = 'addlaser';
    protected $fillable = [
        'company',
        'product_id',
        'modal',
        'logo',
        'image',
        'description',
      
    ];


    
}
