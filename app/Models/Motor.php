<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Motor extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

   
    protected $table = 'motor';
    protected $fillable = [
        'product_id',
        'companyname',
        'image',
        'cuttingway',
       
      
    ];


    
}
