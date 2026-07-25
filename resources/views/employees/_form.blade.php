<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-name">Name <span class="text-danger">*</span></label>
        <input id="employee-name" type="text" name="name" class="form-control" value="{{ old('name', $employee->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-phone">Phone <span class="text-danger">*</span></label>
        <input id="employee-phone" type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-email">Email</label>
        <input id="employee-email" type="email" name="email" class="form-control" value="{{ old('email', $employee->email ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-department">Department</label>
        <input id="employee-department" type="text" name="department" class="form-control" value="{{ old('department', $employee->department ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-designation">Designation</label>
        <input id="employee-designation" type="text" name="designation" class="form-control" value="{{ old('designation', $employee->designation ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-joining-date">Joining Date <span class="text-danger">*</span></label>
        <input id="employee-joining-date" type="date" name="joining_date" class="form-control" value="{{ old('joining_date', isset($employee) ? $employee->joining_date->toDateString() : '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="employee-address">Address</label>
        <textarea id="employee-address" name="address" class="form-control">{{ old('address', $employee->address ?? '') }}</textarea>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-emergency-name">Emergency Contact Name</label>
        <input id="employee-emergency-name" type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $employee->emergency_contact_name ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-emergency-phone">Emergency Contact Phone</label>
        <input id="employee-emergency-phone" type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-pay-type">Pay Type <span class="text-danger">*</span></label>
        <select id="employee-pay-type" name="pay_type" class="form-select" required>
            <option value="daily" @selected(old('pay_type', $employee->pay_type ?? '') === 'daily')>Daily Wage</option>
            <option value="monthly" @selected(old('pay_type', $employee->pay_type ?? '') === 'monthly')>Monthly Salary</option>
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-pay-rate">Pay Rate (₹) <span class="text-danger">*</span></label>
        <input id="employee-pay-rate" type="number" step="0.01" name="pay_rate" class="form-control" value="{{ old('pay_rate', $employee->pay_rate ?? '') }}" required>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-ot-rate">Overtime Rate (₹/hour)</label>
        <input id="employee-ot-rate" type="number" step="0.01" name="overtime_rate_per_hour" class="form-control" value="{{ old('overtime_rate_per_hour', $employee->overtime_rate_per_hour ?? 0) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-bank-holder">Bank Account Holder</label>
        <input id="employee-bank-holder" type="text" name="bank_account_holder_name" class="form-control" value="{{ old('bank_account_holder_name', $employee->bank_account_holder_name ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-bank-account">Bank Account Number</label>
        <input id="employee-bank-account" type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $employee->bank_account_number ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-bank-ifsc">Bank IFSC</label>
        <input id="employee-bank-ifsc" type="text" name="bank_ifsc" class="form-control" value="{{ old('bank_ifsc', $employee->bank_ifsc ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="employee-bank-name">Bank Name</label>
        <input id="employee-bank-name" type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $employee->bank_name ?? '') }}">
    </div>
</div>
