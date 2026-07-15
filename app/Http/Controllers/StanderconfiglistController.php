<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Motor;
use App\Models\Gear;
use App\Models\Rack;
use App\Models\Softerwere1;





class StanderconfiglistController extends Controller
{
    public function motorstore(Request $request)
    {
        return $this->storeConfigRecord($request, Motor::class, 'standerconfiglist');
    }
    public function gearstore(Request $request)
    {
        return $this->storeConfigRecord($request, Gear::class, 'standerconfiglist');
    }
    public function rackstore(Request $request)
    {
        return $this->storeConfigRecord($request, Rack::class, 'standerconfiglist');
    }
    public function softwarestore(Request $request)
    {
        return $this->storeConfigRecord($request, Softerwere1::class, 'standerconfiglist');
    }
    }
   
   

    



