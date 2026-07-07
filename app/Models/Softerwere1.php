<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Softerwere1 extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

   
    protected $table = 'softerwere1';
    protected $fillable = [
        'product_id',
        'companyname',
        'image',
       
       
      
    ];


    
}