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
        $input = $request->all();
        Softerwere::create($input);
        $id = $request->input('product_id');
        return view('standerconfig', compact('id'));
    }
    public function cuttingstore(Request $request)
    {
        $input = $request->all();
        Lasercutting::create($input);
        $id = $request->input('product_id');
        return view('standerconfig', compact('id'));
    }
    public function focusingstore(Request $request)
    {
        $input = $request->all();
        Fource::create($input);
        $id = $request->input('product_id');
        return view('standerconfig', compact('id'));
    }
    public function powerstore(Request $request)
    {
        $input = $request->all();
        Power::create($input);
        $id = $request->input('product_id');
        return view('standerconfig', compact('id'));
    }
    public function softerwereshow(Request $request)
    {
        // Route "softerwere.show" is registered but not yet wired to any view/logic; kept as a no-op stub.
    }
    }
   
   

    



