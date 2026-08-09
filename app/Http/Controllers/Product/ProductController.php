<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductService $service)
    {
    }

    public function index(Request $request)
    {
        $product = $this->service->listWithInventory();
        return view('product', compact('product'));
    }

    public function productstore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:255',
            'make' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'vandername' => 'nullable|string|max:255',
        ]);

        $this->service->createWithInventory($data);

        return redirect()->route('product')->with('success', 'Product created successfully.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:255',
            'make' => 'nullable|string|max:255',
        ]);

        $this->service->update($id, $data);

        return redirect()->route('product')->with('success', 'Product updated successfully.');
    }

    public function delete($id)
    {
        $this->service->delete($id);
        return redirect()->route('product');
    }

    public function toggleSparePart($id)
    {
        $this->service->toggleSparePart($id);
        return redirect()->route('product')->with('success', 'Product updated.');
    }
}
