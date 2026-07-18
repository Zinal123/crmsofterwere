<?php

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

// These 7 routes must be registered before the {any} catch-all below:
// all are single URL segments, so without this ordering the catch-all
// would intercept them first (see app/Http/Controllers/Home/HomeController.php
// @index) and skip both the real controller and this auth check entirely.
// (invoice.create, invoice.histry, invoice, invoice.vender, invoice.inventrylist, product, machines.index)
Route::middleware('auth')->group(function () {
    Route::get('apps-invoices-create' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'create'])->name('invoice.create')->middleware('permission:invoices.create');
    Route::get('paymenthistry', [App\Http\Controllers\Invoice\InvoiceController::class, 'paymenthistry'])->name('invoice.histry')->middleware('permission:payment-history.view');
    Route::get('apps-invoices-list' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'index'])->name('invoice')->middleware('permission:invoices.view');
    Route::get('vender', [App\Http\Controllers\Invoice\InvoiceController::class, 'vender'])->name('invoice.vender')->middleware('permission:vendors.view');
    Route::get('inventrylist', [App\Http\Controllers\Inventory\InventryController::class, 'inventry'])->name('invoice.inventrylist')->middleware('permission:inventory.view');
    Route::get('product' ,[App\Http\Controllers\Product\ProductController::class, 'index'])->name('product')->middleware('permission:products.view');
    Route::get('machines', [App\Http\Controllers\Job\MachineController::class, 'index'])->name('machines.index')->middleware('permission:jobs.manage-machines');
    Route::get('jobs', [App\Http\Controllers\Job\JobController::class, 'index'])->name('jobs.index')->middleware('permission:jobs.view-own|jobs.view-all');
});

Route::get('{any}', [App\Http\Controllers\Home\HomeController::class, 'index'])->name('index');

//Update User Details & Auth-protected routes
Route::middleware('auth')->group(function () {
    Route::post('/update-profile/{id}', [App\Http\Controllers\Home\HomeController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-password/{id}', [App\Http\Controllers\Home\HomeController::class, 'updatePassword'])->name('updatePassword');
    Route::get('/co2quation/{id}' ,[App\Http\Controllers\Quotation\QutationController::class,'Co2quation'])->name('co2quation')->middleware('permission:quotations.view');
    Route::post('/co2quationstore' ,[App\Http\Controllers\Quotation\QutationController::class,'Co2quationstore'])->name('Co2quationstore')->middleware('permission:quotations.create');

    Route::get('apps-invoices-list/data' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'listData'])->name('invoice.data')->middleware('permission:invoices.view');
    Route::get('admin/invoice/getproductvalue' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'getproduct'])->name('invoice.product')->middleware('permission:invoices.create');
    Route::get('admin/invoice/getproduct1' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'getproductvalue1'])->name('invoice.product1')->middleware('permission:invoices.create');
    Route::post('update-payment', [App\Http\Controllers\Invoice\InvoiceController::class, 'updatePayment'])->middleware('permission:invoices.record-payment');
    Route::post('invoicestore' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'store'])->name('invoice.store')->middleware('permission:invoices.create');
    Route::get('invoiceddetails/{id}' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'details'])->name('invoice.details')->middleware('permission:invoices.view-details');

    Route::post('inventrystore', [App\Http\Controllers\Inventory\InventryController::class, 'inventrystore'])->name('inventrystore')->middleware('permission:inventory.create');
    Route::post('quantityupdate', [App\Http\Controllers\Inventory\InventryController::class, 'quantityupdate'])->name('quantityupdate')->middleware('permission:inventory.update');

    Route::post('productstore' ,[App\Http\Controllers\Product\ProductController::class, 'productstore'])->name('productstore')->middleware('permission:products.create');
    Route::get('productdelete/{id}' ,[App\Http\Controllers\Product\ProductController::class, 'delete'])->name('product.delete')->middleware('permission:products.delete');

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

    Route::get('fiberqutation/{id}' ,[App\Http\Controllers\Quotation\QutationController::class, 'generatequtation'])->name('generatequtation')->middleware('permission:quotations.view');
    Route::post('fiberqutation/store' ,[App\Http\Controllers\Quotation\QutationController::class, 'generatequtationstore'])->name('generatequtationstore')->middleware('permission:quotations.create');
    Route::get('admin/listqutation' ,[App\Http\Controllers\Quotation\QutationController::class, 'index'])->name('listqutation')->middleware('permission:quotations.view');
    Route::get('/printquation/{id}', [App\Http\Controllers\Quotation\QutationController::class, 'print'])->name('quation.pdf')->middleware('permission:quotations.download-pdf');

    Route::middleware('permission:admin.manage-roles')->group(function () {
        Route::get('admin/roles', [App\Http\Controllers\Admin\RoleController::class, 'index'])->name('admin.roles.index');
        Route::post('admin/roles', [App\Http\Controllers\Admin\RoleController::class, 'store'])->name('admin.roles.store');
        Route::post('admin/roles/{roleId}/toggle-permission', [App\Http\Controllers\Admin\RoleController::class, 'togglePermission'])->name('admin.roles.togglePermission');
        Route::delete('admin/roles/{id}', [App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('admin.roles.destroy');
    });

    Route::middleware('permission:admin.manage-users')->group(function () {
        Route::get('admin/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
        Route::post('admin/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
        Route::put('admin/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    });

    Route::post('machines', [App\Http\Controllers\Job\MachineController::class, 'store'])->name('machines.store')->middleware('permission:jobs.manage-machines');
    Route::post('machines/{id}/toggle', [App\Http\Controllers\Job\MachineController::class, 'toggle'])->name('machines.toggle')->middleware('permission:jobs.manage-machines');

    Route::get('jobs/create', [App\Http\Controllers\Job\JobController::class, 'create'])->name('jobs.create')->middleware('permission:jobs.create|jobs.assign');
    Route::post('jobs', [App\Http\Controllers\Job\JobController::class, 'store'])->name('jobs.store')->middleware('permission:jobs.create|jobs.assign');
    Route::get('jobs/{id}', [App\Http\Controllers\Job\JobController::class, 'show'])->name('jobs.show')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::get('jobs-pending-approval', [App\Http\Controllers\Job\JobController::class, 'pendingApproval'])->name('jobs.pending-approval')->middleware('permission:jobs.approve');
    Route::post('jobs/{id}/approve', [App\Http\Controllers\Job\JobController::class, 'approve'])->name('jobs.approve')->middleware('permission:jobs.approve');
    Route::post('jobs/{id}/reject', [App\Http\Controllers\Job\JobController::class, 'reject'])->name('jobs.reject')->middleware('permission:jobs.approve');
    Route::post('jobs/{id}/start', [App\Http\Controllers\Job\JobController::class, 'start'])->name('jobs.start')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/hold', [App\Http\Controllers\Job\JobController::class, 'hold'])->name('jobs.hold')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/resume', [App\Http\Controllers\Job\JobController::class, 'resume'])->name('jobs.resume')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/photos', [App\Http\Controllers\Job\JobController::class, 'storePhoto'])->name('jobs.photos.store')->middleware('permission:jobs.view-own|jobs.view-all');
    Route::post('jobs/{id}/complete', [App\Http\Controllers\Job\JobController::class, 'complete'])->name('jobs.complete')->middleware('permission:jobs.view-own|jobs.view-all');
});


