<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use  App\Models\Cnsthinks;
use  App\Models\Cutting;





class TechinalController extends Controller
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
    public function cuttingway(Request $request ,$id)
    {
     $cutting = Cutting::where('product_id' ,$id)->get();
     return view('standerconfig' ,compact('cutting'));
    }
    public function cncthinkness(Request $request ,$id)
    {
     $cnsthinks = Cnsthinks::where('product_id' ,$id)->get();
     return view('standerconfig' ,compact('cnsthinks'));
    }
 
    public function cuttingwaystore(Request $request)
    {
        $input = $request->all();
        Cutting::create($input);
        return view('standerconfig');
    }
    public function cncthinknessstore(Request $request)
    {
        $input = $request->all();
        Cnsthinks::create($input);
        return view('standerconfig');
    }
  
    }
   
   

    



