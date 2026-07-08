<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Paidamount extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

   
    protected $table = 'paidamount';
    protected $fillable = [
        'invoice_id',
        'customer_id',
        'paidAmount'

       
       
      
    ];


    
}
