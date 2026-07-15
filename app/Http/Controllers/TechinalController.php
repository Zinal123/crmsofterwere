<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Models\Cnsthinks;
use  App\Models\Cutting;





class TechinalController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function cuttingway(Request $request ,$id)
    {
     $cutting = Cutting::where('product_id' ,$id)->get();
     return view('standerconfig' ,compact('cutting', 'id'));
    }
    public function cncthinkness(Request $request ,$id)
    {
     $cnsthinks = Cnsthinks::where('product_id' ,$id)->get();
     return view('standerconfig' ,compact('cnsthinks'));
    }
 
    public function cuttingwaystore(Request $request)
    {
        return $this->storeConfigRecord($request, Cutting::class, 'technicalparameters');
    }
    public function cncthinknessstore(Request $request)
    {
        return $this->storeConfigRecord($request, Cnsthinks::class, 'technicalparameters');
    }
  
    }
   
   

    



