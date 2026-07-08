<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Fource extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

   
    protected $table = 'fours';
    protected $fillable = [
        'company',
        'product_id',
        'modal',
        'logo',
        'image',
        'description',
      
    ];


    
}
