<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Quation extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Auditable;

   
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

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id');
    }
}
