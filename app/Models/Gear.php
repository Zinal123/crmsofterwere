<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Gear extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

   
    protected $table = 'gear';
    protected $fillable = [
        'product_id',
        'companyname',
        'image',
       
       
      
    ];


    
}
