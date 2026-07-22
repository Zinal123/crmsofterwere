<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;

class InventryController extends Controller
{
    public function __construct(private InventoryService $service)
    {
    }

    public function inventry()
    {
        // The view (inventrylist.blade.php) re-queries both $Invetry and
        // $product itself via inline PHP, so this passed-in $product is
        // never actually used - pre-existing dead data, preserved as-is
        // rather than fixed here (same pattern as apps-invoices-list and
        // vender before their route-shadowing fix).
        $product = $this->service->getProductList();
        return view('inventrylist', compact('product'));
    }

    public function inventrystore(Request $request)
    {
        // `product_id` required: a missing one previously created an orphan
        // inventory row with no linked product. `vandername`/`rate` weren't
        // part of any reported finding and are left as-is.
        $request->validate([
            'product_id' => 'required|integer|exists:product,id',
            'quantity' => 'nullable|integer',
        ]);

        $this->service->create($request->all());
        return redirect()->route('invoice.inventrylist');
    }

    public function quantityupdate(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:invetry,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $found = $this->service->reduceQuantity($data['id'], $data['quantity']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($found) {
            return response()->json(['success' => true]);
        }
    }
}
