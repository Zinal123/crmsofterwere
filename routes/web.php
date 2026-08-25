<?php

use App\Support\ConfigDefaults;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register' => false]);
//Language Translation
Route::get('index/{locale}', [App\Http\Controllers\Home\HomeController::class, 'lang']);

Route::middleware(['auth', 'permission:dashboard.view'])->get('/', [App\Http\Controllers\Home\HomeController::class, 'root'])->name('root');

// These 13 routes must be registered before the {any} catch-all below:
// all are single URL segments, so without this ordering the catch-all
// would intercept them first (see app/Http/Controllers/Home/HomeController.php
// @index) and skip both the real controller and this auth check entirely.
// (invoice.create, invoice.histry, invoice, invoice.inventrylist, product,
// machines.index, jobs.index, jobs.pending-approval, employees.index, attendance.mark, reports.index,
// expenses.index, payroll.index)
Route::middleware('auth')->group(function () {
    // Registered here (before the {any} catch-all further down) so /profile
    // resolves to the controller with its $user data, not the view directly.
    Route::get('profile', [App\Http\Controllers\Home\HomeController::class, 'profile'])->name('profile');
    Route::get('reports', [App\Http\Controllers\Reporting\ReportController::class, 'index'])->name('reports.index')->middleware('permission:reports.view');
    Route::middleware('permission:accounting.view')->group(function () {
        Route::get('accounting/chart-of-accounts', [App\Http\Controllers\Reporting\AccountingController::class, 'chartOfAccounts'])->name('accounting.chart');
        Route::get('accounting/profit-loss', [App\Http\Controllers\Reporting\AccountingController::class, 'profitAndLoss'])->name('accounting.profit-loss');
        Route::get('accounting/trial-balance', [App\Http\Controllers\Reporting\AccountingController::class, 'trialBalance'])->name('accounting.trial-balance');
    });
    Route::get('expenses', [App\Http\Controllers\Expenses\DailyTransactionController::class, 'index'])->name('expenses.index')->middleware('permission:expenses.view');
    Route::get('apps-invoices-create' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'create'])->name('invoice.create')->middleware('permission:invoices.create');
    Route::get('paymenthistry', [App\Http\Controllers\Invoice\InvoiceController::class, 'paymenthistry'])->name('invoice.histry')->middleware('permission:payment-history.view');
    Route::get('apps-invoices-list' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'index'])->name('invoice')->middleware('permission:invoices.view');
    Route::get('inventrylist', [App\Http\Controllers\Inventory\InventryController::class, 'inventry'])->name('invoice.inventrylist')->middleware('permission:inventory.view');
    Route::get('product' ,[App\Http\Controllers\Product\ProductController::class, 'index'])->name('product')->middleware('permission:products.view');
    Route::get('machines', [App\Http\Controllers\Job\MachineController::class, 'index'])->name('machines.index')->middleware('permission:jobs.manage-machines');
    Route::get('jobs', [App\Http\Controllers\Job\JobController::class, 'index'])->name('jobs.index')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::get('jobs-pending-approval', [App\Http\Controllers\Job\JobController::class, 'pendingApproval'])->name('jobs.pending-approval')->middleware('permission:jobs.approve');
    Route::get('employees', [App\Http\Controllers\Workforce\EmployeeController::class, 'index'])->name('employees.index')->middleware('permission:employees.view');
    Route::get('attendance', [App\Http\Controllers\Workforce\AttendanceController::class, 'mark'])->name('attendance.mark')->middleware('permission:attendance.manage');
    Route::get('payroll', [App\Http\Controllers\Workforce\PayrollController::class, 'index'])->name('payroll.index')->middleware('permission:payroll.view');
});

// Client portal - separate "client" guard, own session, own layout. Registered
// before the {any} catch-all below (like the block above) since /portal on its
// own is a single path segment.
Route::prefix('portal')->name('client.')->group(function () {
    Route::get('login', [App\Http\Controllers\Client\Auth\ClientLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [App\Http\Controllers\Client\Auth\ClientLoginController::class, 'login'])->name('login.attempt');
    Route::post('logout', [App\Http\Controllers\Client\Auth\ClientLoginController::class, 'logout'])->name('logout');

    Route::middleware('auth:client')->group(function () {
        Route::get('/', [App\Http\Controllers\Client\DashboardController::class, 'index'])->name('dashboard');
        Route::get('machines/{machineId}/tickets/create', [App\Http\Controllers\Client\TicketController::class, 'create'])->name('tickets.create');
        Route::post('tickets', [App\Http\Controllers\Client\TicketController::class, 'store'])->name('tickets.store');
        Route::get('tickets', [App\Http\Controllers\Client\TicketController::class, 'index'])->name('tickets.index');
        Route::get('tickets/{id}', [App\Http\Controllers\Client\TicketController::class, 'show'])->name('tickets.show');
        Route::post('push-subscriptions', [App\Http\Controllers\Job\PushSubscriptionController::class, 'storeForClient'])->name('push-subscriptions.store');

        Route::get('machines/{machineId}/spare-parts/create', [App\Http\Controllers\Client\SparePartRequestController::class, 'create'])->name('spare-parts.create');
        Route::post('spare-parts', [App\Http\Controllers\Client\SparePartRequestController::class, 'store'])->name('spare-parts.store');
        Route::get('spare-parts', [App\Http\Controllers\Client\SparePartRequestController::class, 'index'])->name('spare-parts.index');
    });
});

Route::get('{any}', [App\Http\Controllers\Home\HomeController::class, 'index'])->name('index');

//Update User Details & Auth-protected routes
Route::middleware('auth')->group(function () {
    Route::post('/update-profile/{id}', [App\Http\Controllers\Home\HomeController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-password/{id}', [App\Http\Controllers\Home\HomeController::class, 'updatePassword'])->name('updatePassword');
    Route::get('/notifications/all', [App\Http\Controllers\Home\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\Home\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\Home\NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('employees/create', [App\Http\Controllers\Workforce\EmployeeController::class, 'create'])->name('employees.create')->middleware('permission:employees.manage');
    Route::post('employees', [App\Http\Controllers\Workforce\EmployeeController::class, 'store'])->name('employees.store')->middleware('permission:employees.manage');
    Route::get('employees/{id}/edit', [App\Http\Controllers\Workforce\EmployeeController::class, 'edit'])->name('employees.edit')->middleware('permission:employees.manage');
    Route::put('employees/{id}', [App\Http\Controllers\Workforce\EmployeeController::class, 'update'])->name('employees.update')->middleware('permission:employees.manage');
    Route::post('employees/{id}/deactivate', [App\Http\Controllers\Workforce\EmployeeController::class, 'deactivate'])->name('employees.deactivate')->middleware('permission:employees.manage');
    Route::post('employees/{id}/documents', [App\Http\Controllers\Workforce\EmployeeController::class, 'storeDocument'])->name('employees.documents.store')->middleware('permission:employees.manage');
    Route::post('attendance', [App\Http\Controllers\Workforce\AttendanceController::class, 'store'])->name('attendance.store')->middleware('permission:attendance.manage');
    Route::get('attendance/{employee}/register', [App\Http\Controllers\Workforce\AttendanceController::class, 'register'])->name('attendance.register')->middleware('permission:attendance.view');
    Route::get('employees/{employee}/payroll', [App\Http\Controllers\Workforce\PayrollController::class, 'show'])->name('employees.payroll')->middleware('permission:payroll.view');
    Route::post('employees/{employee}/payments', [App\Http\Controllers\Workforce\PayrollController::class, 'storePayment'])->name('employees.payments.store')->middleware('permission:payroll.manage-payments');
    Route::put('employees/{employee}/payments/{paymentId}', [App\Http\Controllers\Workforce\PayrollController::class, 'updatePayment'])->name('employees.payments.update')->middleware('permission:payroll.manage-payments');
    Route::delete('employees/{employee}/payments/{paymentId}', [App\Http\Controllers\Workforce\PayrollController::class, 'destroyPayment'])->name('employees.payments.destroy')->middleware('permission:payroll.manage-payments');
    Route::get('admin/audit-logs', [App\Http\Controllers\Auditing\AuditLogController::class, 'index'])->name('admin.audit-logs.index');
    Route::get('audit-logs/{type}/{id}', [App\Http\Controllers\Auditing\AuditLogController::class, 'forRecord'])->name('audit-logs.for-record');

    Route::get('/co2quation/{id}' ,[App\Http\Controllers\Quotation\QutationController::class,'Co2quation'])->name('co2quation')->middleware('permission:quotations.view');
    Route::post('/co2quationstore' ,[App\Http\Controllers\Quotation\QutationController::class,'Co2quationstore'])->name('Co2quationstore')->middleware('permission:quotations.create');

    Route::get('apps-invoices-list/data' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'listData'])->name('invoice.data')->middleware('permission:invoices.view');
    Route::get('admin/invoice/getproductvalue' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'getproduct'])->name('invoice.product')->middleware('permission:invoices.create');
    Route::get('admin/invoice/getproduct1' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'getproductvalue1'])->name('invoice.product1')->middleware('permission:invoices.create');
    Route::post('update-payment', [App\Http\Controllers\Invoice\InvoiceController::class, 'updatePayment'])->middleware('permission:invoices.record-payment');
    Route::post('invoicestore' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'store'])->name('invoice.store')->middleware('permission:invoices.create');
    Route::get('invoiceddetails/{id}' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'details'])->name('invoice.details')->middleware('permission:invoices.view-details');
    Route::get('invoices/{id}/pdf', [App\Http\Controllers\Invoice\InvoiceController::class, 'pdf'])->name('invoice.pdf')->middleware('permission:invoices.view-details');
    Route::put('invoices/{id}', [App\Http\Controllers\Invoice\InvoiceController::class, 'update'])->name('invoice.update')->middleware('permission:invoices.update');
    Route::delete('invoices/{id}', [App\Http\Controllers\Invoice\InvoiceController::class, 'destroy'])->name('invoice.destroy')->middleware('permission:invoices.delete');
    Route::post('invoices/bulk-delete', [App\Http\Controllers\Invoice\InvoiceController::class, 'bulkDelete'])->name('invoice.bulk-delete')->middleware('permission:invoices.delete');

    Route::post('inventrystore', [App\Http\Controllers\Inventory\InventryController::class, 'inventrystore'])->name('inventrystore')->middleware('permission:inventory.create');
    Route::post('quantityupdate', [App\Http\Controllers\Inventory\InventryController::class, 'quantityupdate'])->name('quantityupdate')->middleware('permission:inventory.update');
    Route::post('inventory/{id}/low-stock-threshold', [App\Http\Controllers\Inventory\InventryController::class, 'setLowStockThreshold'])->name('inventory.set-low-stock-threshold')->middleware('permission:inventory.update');

    Route::post('productstore' ,[App\Http\Controllers\Product\ProductController::class, 'productstore'])->name('productstore')->middleware('permission:products.create');
    Route::get('productdelete/{id}' ,[App\Http\Controllers\Product\ProductController::class, 'delete'])->name('product.delete')->middleware('permission:products.delete');
    Route::post('products/bulk-delete' ,[App\Http\Controllers\Product\ProductController::class, 'bulkDelete'])->name('product.bulk-delete')->middleware('permission:products.delete');
    Route::put('product/{id}' ,[App\Http\Controllers\Product\ProductController::class, 'update'])->name('product.update')->middleware('permission:products.update');
    Route::post('product/{id}/toggle-spare-part', [App\Http\Controllers\Product\ProductController::class, 'toggleSparePart'])->name('product.toggle-spare-part')->middleware('permission:spare-parts.manage');

    Route::get('standerconfig/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'standerconfig'])->name('standerconfig')->middleware('permission:products.manage-config');
    Route::get('TechnicalParameters/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'technicalparameters'])->name('TechnicalParameters')->middleware('permission:products.manage-config');
    Route::get('standerconfiglist/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'standerconfiglist'])->name('standerconfiglist')->middleware('permission:products.manage-config');

    Route::get('softerwere/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softerwere'])->name('softerwere')->middleware('permission:products.manage-config');
    Route::post('softerwere/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softerwerestore'])->name('softerwerestore')->middleware('permission:products.manage-config');
    Route::post('cutting/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingstore'])->name('cuttingstore')->middleware('permission:products.manage-config');
    Route::post('focusing/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'focusingstore'])->name('focusingstore')->middleware('permission:products.manage-config');
    Route::post('power/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'powerstore'])->name('powerstore')->middleware('permission:products.manage-config');
    Route::get('softerwere/show' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softerwereshow'])->name('softerwere.show')->middleware('permission:products.manage-config');

    Route::post('cuttingway/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingwaystore'])->name('cuttingwaystore')->middleware('permission:products.manage-config');
    Route::post('cncthinkness/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cncthinknessstore'])->name('cncthinknessstore')->middleware('permission:products.manage-config');
    Route::get('cuttingway/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingway'])->name('cuttingway')->middleware('permission:products.manage-config');

    Route::post('motor/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'motorstore'])->name('motorstore')->middleware('permission:products.manage-config');
    Route::post('gear/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'gearstore'])->name('gearstore')->middleware('permission:products.manage-config');
    Route::post('rack/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'rackstore'])->name('rackstore')->middleware('permission:products.manage-config');
    Route::post('Software/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softwarestore'])->name('softwarestore')->middleware('permission:products.manage-config');
});

Route::middleware('auth')->group(function () {

    Route::post('softerwere/{id}/update' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softerwereupdate'])->name('softerwereupdate')->middleware('permission:products.manage-config');
    Route::post('softerwere/{id}/delete' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softereweredelete'])->name('softereweredelete')->middleware('permission:products.manage-config');
    Route::post('cutting/{id}/update' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingupdate'])->name('cuttingupdate')->middleware('permission:products.manage-config');
    Route::post('cutting/{id}/delete' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingdelete'])->name('cuttingdelete')->middleware('permission:products.manage-config');
    Route::post('focusing/{id}/update' ,[App\Http\Controllers\Product\ProductConfigController::class, 'focusingupdate'])->name('focusingupdate')->middleware('permission:products.manage-config');
    Route::post('focusing/{id}/delete' ,[App\Http\Controllers\Product\ProductConfigController::class, 'focusingdelete'])->name('focusingdelete')->middleware('permission:products.manage-config');
    Route::post('power/{id}/update' ,[App\Http\Controllers\Product\ProductConfigController::class, 'powerupdate'])->name('powerupdate')->middleware('permission:products.manage-config');
    Route::post('power/{id}/delete' ,[App\Http\Controllers\Product\ProductConfigController::class, 'powerdelete'])->name('powerdelete')->middleware('permission:products.manage-config');
    Route::post('motor/{id}/update' ,[App\Http\Controllers\Product\ProductConfigController::class, 'motorupdate'])->name('motorupdate')->middleware('permission:products.manage-config');
    Route::post('motor/{id}/delete' ,[App\Http\Controllers\Product\ProductConfigController::class, 'motordelete'])->name('motordelete')->middleware('permission:products.manage-config');
    Route::post('gear/{id}/update' ,[App\Http\Controllers\Product\ProductConfigController::class, 'gearupdate'])->name('gearupdate')->middleware('permission:products.manage-config');
    Route::post('gear/{id}/delete' ,[App\Http\Controllers\Product\ProductConfigController::class, 'geardelete'])->name('geardelete')->middleware('permission:products.manage-config');
    Route::post('rack/{id}/update' ,[App\Http\Controllers\Product\ProductConfigController::class, 'rackupdate'])->name('rackupdate')->middleware('permission:products.manage-config');
    Route::post('rack/{id}/delete' ,[App\Http\Controllers\Product\ProductConfigController::class, 'rackdelete'])->name('rackdelete')->middleware('permission:products.manage-config');
    Route::post('software1/{id}/update' ,[App\Http\Controllers\Product\ProductConfigController::class, 'software1update'])->name('software1update')->middleware('permission:products.manage-config');
    Route::post('software1/{id}/delete' ,[App\Http\Controllers\Product\ProductConfigController::class, 'software1delete'])->name('software1delete')->middleware('permission:products.manage-config');
    Route::post('cuttingway/{id}/update' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingwayupdate'])->name('cuttingwayupdate')->middleware('permission:products.manage-config');
    Route::post('cuttingway/{id}/delete' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingwaydelete'])->name('cuttingwaydelete')->middleware('permission:products.manage-config');
    Route::post('cncthinkness/{id}/update' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cncthinknessupdate'])->name('cncthinknessupdate')->middleware('permission:products.manage-config');
    Route::post('cncthinkness/{id}/delete' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cncthinknessdelete'])->name('cncthinknessdelete')->middleware('permission:products.manage-config');

    Route::get('fiberqutation/{id}' ,[App\Http\Controllers\Quotation\QutationController::class, 'generatequtation'])->name('generatequtation')->middleware('permission:quotations.view');
    Route::post('fiberqutation/store' ,[App\Http\Controllers\Quotation\QutationController::class, 'generatequtationstore'])->name('generatequtationstore')->middleware('permission:quotations.create');
    Route::get('admin/listqutation' ,[App\Http\Controllers\Quotation\QutationController::class, 'index'])->name('listqutation')->middleware('permission:quotations.view');
    Route::get('/printquation/{id}', [App\Http\Controllers\Quotation\QutationController::class, 'print'])->name('quation.pdf')->middleware('permission:quotations.download-pdf');
    Route::get('/printquation/{id}/print', [App\Http\Controllers\Quotation\QutationController::class, 'printInline'])->name('quation.print')->middleware('permission:quotations.download-pdf');
    Route::get('quation/delete/{id}', [App\Http\Controllers\Quotation\QutationController::class, 'delete'])->name('quation.delete')->middleware('permission:quotations.delete');
    Route::put('quation/{id}', [App\Http\Controllers\Quotation\QutationController::class, 'update'])->name('quation.update')->middleware('permission:quotations.update');
    Route::post('quation/{id}/convert-to-invoice', [App\Http\Controllers\Quotation\QutationController::class, 'convertToInvoice'])->name('quation.convert-to-invoice')->middleware(['permission:quotations.update', 'permission:invoices.create']);

    Route::middleware('permission:admin.manage-roles')->group(function () {
        Route::get('admin/roles', [App\Http\Controllers\Admin\RoleController::class, 'index'])->name('admin.roles.index');
        Route::post('admin/roles', [App\Http\Controllers\Admin\RoleController::class, 'store'])->name('admin.roles.store');
        Route::post('admin/roles/{roleId}/toggle-permission', [App\Http\Controllers\Admin\RoleController::class, 'togglePermission'])->name('admin.roles.togglePermission');
        Route::delete('admin/roles/{id}', [App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('admin.roles.destroy');
        Route::put('admin/roles/{id}', [App\Http\Controllers\Admin\RoleController::class, 'update'])->name('admin.roles.update');
    });

    Route::middleware('permission:admin.manage-users')->group(function () {
        Route::get('admin/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
        Route::post('admin/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
        Route::put('admin/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
        Route::put('admin/users/{id}/profile', [App\Http\Controllers\Admin\UserController::class, 'updateProfile'])->name('admin.users.update-profile');
    });

    Route::middleware('permission:client-machines.manage')->group(function () {
        Route::get('admin/client-accounts', [App\Http\Controllers\Ticketing\ClientAccountController::class, 'index'])->name('admin.client-accounts.index');
        Route::post('admin/client-accounts', [App\Http\Controllers\Ticketing\ClientAccountController::class, 'store'])->name('admin.client-accounts.store');
        Route::put('admin/client-accounts/{id}', [App\Http\Controllers\Ticketing\ClientAccountController::class, 'update'])->name('admin.client-accounts.update');
        Route::post('admin/client-accounts/{id}/toggle', [App\Http\Controllers\Ticketing\ClientAccountController::class, 'toggle'])->name('admin.client-accounts.toggle');
    });

    Route::middleware('permission:client-machines.view')->get('admin/client-machines', [App\Http\Controllers\Ticketing\ClientMachineController::class, 'index'])->name('admin.client-machines.index');
    Route::middleware('permission:client-machines.manage')->post('admin/client-machines', [App\Http\Controllers\Ticketing\ClientMachineController::class, 'store'])->name('admin.client-machines.store');
    Route::middleware('permission:client-machines.manage')->put('admin/client-machines/{id}', [App\Http\Controllers\Ticketing\ClientMachineController::class, 'update'])->name('admin.client-machines.update');

    Route::middleware('permission:vendors.view')->get('admin/vendors', [App\Http\Controllers\Vendor\VendorController::class, 'index'])->name('admin.vendors.index');
    Route::middleware('permission:vendors.manage')->group(function () {
        Route::post('admin/vendors', [App\Http\Controllers\Vendor\VendorController::class, 'store'])->name('admin.vendors.store');
        Route::put(ConfigDefaults::VENDOR_ID_ROUTE, [App\Http\Controllers\Vendor\VendorController::class, 'update'])->name('admin.vendors.update');
        Route::delete(ConfigDefaults::VENDOR_ID_ROUTE, [App\Http\Controllers\Vendor\VendorController::class, 'destroy'])->name('admin.vendors.destroy');
    });
    Route::middleware('permission:vendor-payments.view')->get('admin/vendor-payments', [App\Http\Controllers\Vendor\VendorController::class, 'paymentsIndex'])->name('vendor-payments.index');
    Route::middleware('permission:vendor-payments.view')->get(ConfigDefaults::VENDOR_ID_ROUTE, [App\Http\Controllers\Vendor\VendorController::class, 'show'])->name('admin.vendors.show');
    Route::middleware('permission:vendor-payments.manage')->group(function () {
        Route::post('admin/vendors/{id}/bills', [App\Http\Controllers\Vendor\VendorController::class, 'storeBill'])->name('admin.vendors.bills.store');
        Route::put('admin/vendors/{id}/bills/{billId}', [App\Http\Controllers\Vendor\VendorController::class, 'updateBill'])->name('admin.vendors.bills.update');
        Route::delete('admin/vendors/{id}/bills/{billId}', [App\Http\Controllers\Vendor\VendorController::class, 'destroyBill'])->name('admin.vendors.bills.destroy');
        Route::post('admin/vendors/{id}/payments', [App\Http\Controllers\Vendor\VendorController::class, 'storePayment'])->name('admin.vendors.payments.store');
        Route::put('admin/vendors/{id}/payments/{paymentId}', [App\Http\Controllers\Vendor\VendorController::class, 'updatePaymentRecord'])->name('admin.vendors.payments.update');
        Route::delete('admin/vendors/{id}/payments/{paymentId}', [App\Http\Controllers\Vendor\VendorController::class, 'destroyPaymentRecord'])->name('admin.vendors.payments.destroy');
    });
});

Route::middleware('auth')->group(function () {

    Route::middleware('permission:checklist-templates.manage')->group(function () {
        Route::get('admin/checklist-templates', [App\Http\Controllers\Admin\ChecklistTemplateController::class, 'index'])->name('admin.checklist-templates.index');
        Route::post('admin/checklist-templates', [App\Http\Controllers\Admin\ChecklistTemplateController::class, 'store'])->name('admin.checklist-templates.store');
        Route::put('admin/checklist-templates/{id}', [App\Http\Controllers\Admin\ChecklistTemplateController::class, 'update'])->name('admin.checklist-templates.update');
        Route::delete('admin/checklist-templates/{id}', [App\Http\Controllers\Admin\ChecklistTemplateController::class, 'destroy'])->name('admin.checklist-templates.destroy');
        Route::post('admin/checklist-templates/{id}/items', [App\Http\Controllers\Admin\ChecklistTemplateController::class, 'storeItem'])->name('admin.checklist-templates.items.store');
        Route::delete('admin/checklist-templates/items/{itemId}', [App\Http\Controllers\Admin\ChecklistTemplateController::class, 'destroyItem'])->name('admin.checklist-templates.items.destroy');
    });

    Route::middleware('permission:ticket-problem-types.manage')->group(function () {
        Route::get('admin/ticket-problem-types', [App\Http\Controllers\Ticketing\TicketProblemTypeController::class, 'index'])->name('admin.ticket-problem-types.index');
        Route::post('admin/ticket-problem-types', [App\Http\Controllers\Ticketing\TicketProblemTypeController::class, 'store'])->name('admin.ticket-problem-types.store');
        Route::post('admin/ticket-problem-types/{id}/toggle', [App\Http\Controllers\Ticketing\TicketProblemTypeController::class, 'toggle'])->name('admin.ticket-problem-types.toggle');
    });

    Route::middleware('permission:expenses.manage')->group(function () {
        Route::get('admin/expense-categories', [App\Http\Controllers\Expenses\ExpenseCategoryController::class, 'index'])->name('admin.expense-categories.index');
        Route::post('admin/expense-categories', [App\Http\Controllers\Expenses\ExpenseCategoryController::class, 'store'])->name('admin.expense-categories.store');
        Route::post('admin/expense-categories/{id}/toggle', [App\Http\Controllers\Expenses\ExpenseCategoryController::class, 'toggle'])->name('admin.expense-categories.toggle');
        Route::post('admin/expense-categories/quick-add', [App\Http\Controllers\Expenses\ExpenseCategoryController::class, 'quickAdd'])->name('admin.expense-categories.quick-add');
    });

    Route::middleware('permission:expenses.manage')->post('expenses', [App\Http\Controllers\Expenses\DailyTransactionController::class, 'store'])->name('expenses.store');
    Route::middleware('permission:expenses.view')->get('expenses/cashbook', [App\Http\Controllers\Expenses\DailyTransactionController::class, 'cashBook'])->name('expenses.cashbook');
    Route::middleware('permission:expenses.delete')->delete('expenses/{id}', [App\Http\Controllers\Expenses\DailyTransactionController::class, 'destroy'])->name('expenses.destroy');

    Route::middleware('permission:tickets.view')->group(function () {
        Route::get('admin/tickets', [App\Http\Controllers\Ticketing\TicketController::class, 'index'])->name('admin.tickets.index');
        Route::get('admin/tickets/{id}', [App\Http\Controllers\Ticketing\TicketController::class, 'show'])->name('admin.tickets.show');
    });
    Route::middleware('permission:tickets.assign')->post('admin/tickets/{id}/assign', [App\Http\Controllers\Ticketing\TicketController::class, 'assign'])->name('admin.tickets.assign');

    Route::middleware('permission:spare-part-requests.view')->get('admin/spare-part-requests', [App\Http\Controllers\Ticketing\SparePartRequestController::class, 'index'])->name('admin.spare-part-requests.index');
    Route::middleware('permission:spare-part-requests.manage')->post('admin/spare-part-requests/{id}/status', [App\Http\Controllers\Ticketing\SparePartRequestController::class, 'updateStatus'])->name('admin.spare-part-requests.update-status');

    Route::post('machines', [App\Http\Controllers\Job\MachineController::class, 'store'])->name('machines.store')->middleware('permission:jobs.manage-machines');
    Route::post('machines/{id}/toggle', [App\Http\Controllers\Job\MachineController::class, 'toggle'])->name('machines.toggle')->middleware('permission:jobs.manage-machines');
    Route::put('machines/{id}', [App\Http\Controllers\Job\MachineController::class, 'update'])->name('machines.update')->middleware('permission:jobs.manage-machines');
    Route::post('machines/{id}/flag-down', [App\Http\Controllers\Job\MachineController::class, 'flagDown'])->name('machines.flag-down')->middleware('permission:jobs.view-own|jobs.view-all');

    Route::get('jobs/create', [App\Http\Controllers\Job\JobController::class, 'create'])->name('jobs.create')->middleware('permission:jobs.create|jobs.assign');
    Route::post('jobs', [App\Http\Controllers\Job\JobController::class, 'store'])->name('jobs.store')->middleware('permission:jobs.create|jobs.assign');
    Route::get('jobs/{id}', [App\Http\Controllers\Job\JobController::class, 'show'])->name('jobs.show')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::get('jobs/{id}/pdf', [App\Http\Controllers\Job\JobController::class, 'pdf'])->name('jobs.pdf')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/approve', [App\Http\Controllers\Job\JobController::class, 'approve'])->name('jobs.approve')->middleware('permission:jobs.approve');
    Route::post('jobs/{id}/reject', [App\Http\Controllers\Job\JobController::class, 'reject'])->name('jobs.reject')->middleware('permission:jobs.approve');
    Route::post('jobs/{id}/start', [App\Http\Controllers\Job\JobController::class, 'start'])->name('jobs.start')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/hold', [App\Http\Controllers\Job\JobController::class, 'hold'])->name('jobs.hold')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/resume', [App\Http\Controllers\Job\JobController::class, 'resume'])->name('jobs.resume')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/photos', [App\Http\Controllers\Job\JobController::class, 'storePhoto'])->name('jobs.photos.store')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/checklist', [App\Http\Controllers\Job\JobController::class, 'addChecklistItem'])->name('jobs.checklist.store')->middleware('permission:jobs.view-own|jobs.view-all|jobs.assign');
    Route::post('jobs/{job}/apply-template', [App\Http\Controllers\Job\JobController::class, 'applyTemplate'])->name('jobs.apply-template')->middleware('permission:jobs.view-own|jobs.view-all|jobs.assign');
    Route::post('jobs/{id}/checklist/{itemId}/toggle', [App\Http\Controllers\Job\JobController::class, 'toggleChecklistItem'])->name('jobs.checklist.toggle')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/complete', [App\Http\Controllers\Job\JobController::class, 'complete'])->name('jobs.complete')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/reassign', [App\Http\Controllers\Job\JobController::class, 'reassign'])->name('jobs.reassign')->middleware('permission:jobs.assign');

    Route::middleware('permission:jobs.manage-materials')->group(function () {
        Route::post('jobs/{job}/materials', [App\Http\Controllers\Job\JobMaterialController::class, 'addMaterial'])->name('jobs.materials.store');
        Route::delete('jobs/materials/{material}', [App\Http\Controllers\Job\JobMaterialController::class, 'removeMaterial'])->name('jobs.materials.destroy');
        Route::post('jobs/materials/{material}/substitute', [App\Http\Controllers\Job\JobMaterialController::class, 'substituteMaterial'])->name('jobs.materials.substitute');
    });

    Route::post('push-subscriptions', [App\Http\Controllers\Job\PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
});


