<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\Motor;
use App\Models\Gear;
use App\Models\Rack;
use App\Models\Softerwere1;





class StanderconfiglistController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function softerwere(Request $request ,$id)
    {
     $softwere = Softerwere::where('product_id' ,$id)->get();
     return view('standerconfiglist' ,compact('softwere'));
    }
    public function lasercutting(Request $request)
    {
     $lasercutting = Lasercutting::orderBy('id' ,'desc')->get();
     return view('standerconfiglist' ,compact('lasercutting'));
    }
    public function Focusing(Request $request)
    {
     $focusing  = Fource::orderBy('id' ,'desc')->get();
     return view('standerconfiglist.' ,compact('focusing'));
    }
    public function power(Request $request)
    {
     return view('standerconfiglist.' ,compact('Power'));
    }
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
   
   

    



