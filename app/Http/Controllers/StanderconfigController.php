<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use  App\Models\Softerwere;
use  App\Models\Lasercutting;
use  App\Models\Fource;
use  App\Models\Power;




class StanderconfigController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function softerwere(Request $request ,$id)
    {
     $softwere = Softerwere::where('product_id' ,$id)->get();
     return view('standerconfig' ,compact('softwere'));
    }
    public function lasercutting(Request $request)
    {
     $lasercutting = Lasercutting::orderBy('id' ,'desc')->get();
     return view('standerconfig' ,compact('lasercutting'));
    }
    public function Focusing(Request $request)
    {
     $focusing  = Fource::orderBy('id' ,'desc')->get();
     return view('standerconfig.' ,compact('focusing'));
    }
    public function power(Request $request)
    {
     return view('standerconfig.' ,compact('Power'));
    }
    public function softerwerestore(Request $request)
    {
        return $this->storeConfigRecord($request, Softerwere::class, 'standerconfig');
    }
    public function cuttingstore(Request $request)
    {
        return $this->storeConfigRecord($request, Lasercutting::class, 'standerconfig');
    }
    public function focusingstore(Request $request)
    {
        return $this->storeConfigRecord($request, Fource::class, 'standerconfig');
    }
    public function powerstore(Request $request)
    {
        return $this->storeConfigRecord($request, Power::class, 'standerconfig');
    }
    public function softerwereshow(Request $request)
    {
        // Route "softerwere.show" is registered but not yet wired to any view/logic; kept as a no-op stub.
    }
    }
   
   

    



