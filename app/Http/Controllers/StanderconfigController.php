<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
     return view('standerconfig' ,compact('softwere', 'id'));
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
   
   

    



