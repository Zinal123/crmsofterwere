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
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

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
     $Lasercutting = Lasercutting::orderBy('id' ,'desc')->get();
     return view('standerconfig' ,compact('Lasercutting'));
    }
    public function Focusing(Request $request)
    {
     $Focusing  = Fource::orderBy('id' ,'desc')->get();
     return view('standerconfig.' ,compact('Focusing'));
    }
    public function power(Request $request)
    {
     $power = Power::orderBy('id' ,'desc')->get();
     return view('standerconfig.' ,compact('Power'));
    }
    public function softerwerestore(Request $request)
    {
        $input = $request->all();
        Softerwere::create($input);
        return view('standerconfig');
    }
    public function cuttingstore(Request $request)
    {
        $input = $request->all();
        Lasercutting::create($input);
        return view('standerconfig');
    }          
    public function focusingstore(Request $request)
    {
        $input = $request->all();
        Fource::create($input);
        return view('standerconfig');
    }     
    public function powerstore(Request $request)
    {
        $input = $request->all();
        Power::create($input);
        return view('standerconfig');
    } 
    public function softerwereshow(Request $request)
    {
        // Route "softerwere.show" is registered but not yet wired to any view/logic; kept as a no-op stub.
    }
    }
   
   

    



