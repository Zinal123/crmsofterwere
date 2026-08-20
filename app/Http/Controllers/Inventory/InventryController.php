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
        // Product and Inventory are now one combined page (stock quantity
        // shows inline on the product list) - this route is kept only so
        // old bookmarks/links to /inventrylist don't 404.
        return redirect()->route('product');
    }

    public function inventrystore(Request $request)
    {
        // `product_id` required: a missing one previously created an orphan
        // inventory row with no linked product. `vandername`/`rate` weren't
        // part of any reported finding and are left as-is.
        $request->validate([
            'product_id' => 'required|integer|exists:product,id',
            'quantity' => 'nullable|integer',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
        ]);

        $this->service->create($request->all());
        return redirect()->route('product')->with('success', 'Stock added.');
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

    public function setLowStockThreshold(Request $request, $id)
    {
        $data = $request->validate([
            'low_stock_threshold' => 'nullable|integer|min:0',
        ]);

        $this->service->setLowStockThreshold((int) $id, $data['low_stock_threshold'] ?? null);

        return redirect()->route('product')->with('success', 'Low-stock threshold updated.');
    }
}
