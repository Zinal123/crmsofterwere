<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Rack extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

   
    protected $table = 'rack';
    protected $fillable = [
        'product_id',
        'companyname',
        'image',
        
       
      
    ];


    
}
