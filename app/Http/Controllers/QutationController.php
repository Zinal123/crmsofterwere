<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quation;
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
   
   

    



