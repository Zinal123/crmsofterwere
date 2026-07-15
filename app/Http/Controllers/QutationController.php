<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

use App\Models\Product;
use App\Models\Lasercutting;
use App\Models\Fource;
use App\Models\Power;
use App\Models\Softerwere;
use App\Models\Cutting;
use App\Models\Cnsthinks;
use App\Models\Motor;
use App\Models\Gear;
use App\Models\Quation;
use App\Models\Rack;
use App\Models\Softerwere1;
class QutationController extends Controller
{
    public function index(Request $request)
    {
     $product = Quation::orderBy('id' ,'desc')->get();
     return view('listquation' ,compact('product'));
    }
    public function generatequtation($id)
    {
    return view('qutation');
    }
    public function generatequtationstore(Request $request)
    {
       $input =$request->All();
       Quation::create($input);
       return redirect()->route('listqutation')->with('success', 'Your message has been sent successfully!');
    }
    public function print()
    {
      return view('queationpdf');
      
    }
    public function Co2quation($id)
    {
    return view('co2qutation');
    }
            
       
    }
   
   

    



