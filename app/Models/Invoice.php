<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Support\Auditing\Auditable;

class Invoice extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $table  ="invoice";
    protected $fillable = [
        'invoice_id',
        'date',
        'totalamountbeforetax',
        'amount',
        'amountwithtax',
        'bankaccountnumber',
        'bankifsccode',
        'accountholder',
        'bankname',
        'bankbranchname',
        'notes',
        'paycondition',
        'duedate',
        'placesupply',
        'challanno',
        'pono',
        'ewaybillno',
        'ewaybilldate',
        'despatchthrough',
        'TransportVehicleNo',
        'status',
        'paidamount',
        'remaining_amount',
        'invoicetype',
        'customer_id'

        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
