<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Quation extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

   
    protected $table = 'quationform';
    protected $fillable = [
        'product_id',
        'clientname',
        'companyname',
        'gstno',
        'companyaddress',
        'bank',
        'email',
        'phone',
        'date',
        'reminderdate',
        'softweredetails',
        'lasercutting',
        'focus',
        'power',
        'inputpower',
        'cuttingway',
        'cncspan',
        'cnslenght',
        'cuttingrang',
        'liftingheight',
        'headquantity',
        'cuttingthickess',
        'strokespeed',
        'cuttingspeed',
        'drive',
        'motor',
        'motortype',
        'gearbox',
        'rack',
        'software',
        'description',
        'description1',
        'description2',
        'amount',
        'amount1',
        'amount2',
        'optionparthyscope',
        'note',
      
    ];


    
}
