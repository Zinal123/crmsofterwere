<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Invetry;

class InventryController extends Controller
{
  public function inventry()
  {
     $product = Product::orderBy('id' ,'desc')->get(); 
     return view('inventrylist' ,compact('product')) ;
  }
  public function inventrystore(Request $request)
  {
        $input = $request->all();
        Invetry::create($input);
        return redirect()->route('invoice.inventrylist');
  }
  public function quantityupdate(Request $request)
  {
    $itemId = $request->input('id');
    $quantity = $request->input('quantity');
    $item = Invetry::find($itemId);
    // $input2['quantity'] = $request->input('quantity');
    if ($item) 
    {
                // Calculate the new total paid amount by adding the new payment to the existing paid amount
                $newquantity = $item->quantity  - $quantity;
                $item->quantity = $newquantity ;
                $item->save();
                // Return a success response
                return response()->json(['success' => true]);
    }
  }
}
