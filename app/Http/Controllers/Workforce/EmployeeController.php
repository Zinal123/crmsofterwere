<?php

namespace App\Http\Controllers\Workforce;

use App\Http\Controllers\Controller;
use App\Services\Workforce\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $service,
        private \App\Services\Workforce\EmployeeDocumentService $documentService,
    ) {
    }

    public function index()
    {
        return view('employees.index', ['employees' => $this->service->list()]);
    }

    public function create()
    {
        return view('employees.create');
    }

    private function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'joining_date' => 'required|date',
            'pay_type' => 'required|in:monthly,daily',
            'pay_rate' => 'required|numeric|min:0',
            'overtime_rate_per_hour' => 'nullable|numeric|min:0',
            'bank_account_holder_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:255',
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());

        $this->service->create($data);

        return redirect()->route('employees.index')->with('success', 'Employee added.');
    }

    public function edit($id)
    {
        $employee = $this->service->find($id);
        abort_if(! $employee, 404);

        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate($this->validationRules());

        $this->service->update($id, $data);

        return redirect()->route('employees.index')->with('success', 'Employee updated.');
    }

    public function deactivate($id)
    {
        $this->service->deactivate($id);

        return redirect()->route('employees.index')->with('success', 'Employee deactivated.');
    }

    public function storeDocument(Request $request, $id)
    {
        $data = $request->validate([
            'document_type' => 'required|in:aadhar,pan,driving_license,voter_id,other',
            'document_number' => 'nullable|string|max:100',
            // Bounded to 10MB, comfortably inside php.ini's own
            // upload_max_filesize/post_max_size ceiling - Sonar can't see
            // that ini-level cap, only this rule, hence the NOSONAR.
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // NOSONAR php:S5693
        ]);
        $employee = $this->service->find($id);
        abort_if(! $employee, 404);

        $this->documentService->upload($employee, $request->user(), $request->file('document'), $data['document_type'], $data['document_number'] ?? null);

        return redirect()->route('employees.edit', $employee->id)->with('success', 'Document uploaded.');
    }
}
