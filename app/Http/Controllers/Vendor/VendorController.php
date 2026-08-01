<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\Vendor\VendorService;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct(private VendorService $service)
    {
    }

    public function index()
    {
        return view('vendor.index', [
            'vendors' => $this->service->listAll(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->service->create($data);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor created.');
    }

    public function update(Request $request, $id)
    {
        $vendor = $this->service->find((int) $id);
        abort_if(! $vendor, 404);

        $data = $this->validated($request);
        $this->service->update($vendor, $data);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated.');
    }

    public function destroy($id)
    {
        $vendor = $this->service->find((int) $id);
        abort_if(! $vendor, 404);

        $this->service->delete($vendor);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', Vendor::CATEGORIES),
            'gstin' => 'nullable|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);
    }
}
