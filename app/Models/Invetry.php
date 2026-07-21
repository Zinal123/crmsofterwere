<?php

namespace App\Models;

use App\Support\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invetry extends Model
{
    use HasFactory, Auditable;
    protected $table  ="invetry";
    protected $fillable = [
        'product_id',
        'quantity',
        'vandername',
        'rate',
        
    ];
}
