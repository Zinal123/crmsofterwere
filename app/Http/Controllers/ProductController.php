<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;






class ProductController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
     $product = Product::orderBy('id' ,'desc')->get();
     return view('product' ,compact('product'));
    }
    public function productstore(Request $request)
    {
        $input = $request->all();
        Product::create($input);
        return redirect()->route('product');
    }
    public function standerconfig($id)
    {
        $viewId = 'id';
        return view('standerconfig' ,['id' => $viewId]);
    }
    public function technicalparameters($id)
    {
       
        return view('technicalparameters');
    }
    public function standerconfiglist($id)
    {

        return view('standerconfiglist');
    }
    public function delete($id)
    {
        $cliente = product::find($id);
        $cliente->delete();

        return redirect()->route('product');
    }
               
            
       
    }
   
   

    



