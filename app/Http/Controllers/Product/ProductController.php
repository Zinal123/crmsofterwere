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
        $product = $this->service->list();
        return view('product', compact('product'));
    }

    public function productstore(Request $request)
    {
        $this->service->create($request->all());
        return redirect()->route('product');
    }

    public function delete($id)
    {
        $this->service->delete($id);
        return redirect()->route('product');
    }
}
