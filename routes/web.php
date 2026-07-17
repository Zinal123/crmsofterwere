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

Auth::routes();
//Language Translation
Route::get('index/{locale}', [App\Http\Controllers\Home\HomeController::class, 'lang']);

Route::middleware('auth')->get('/', [App\Http\Controllers\Home\HomeController::class, 'root'])->name('root');

// These 5 routes must be registered before the {any} catch-all below:
// all are single URL segments, so without this ordering the catch-all
// would intercept them first (see app/Http/Controllers/Home/HomeController.php
// @index) and skip both the real controller and this auth check entirely.
Route::middleware('auth')->group(function () {
    Route::get('apps-invoices-create' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'create'])->name('invoice.create');
    Route::get('paymenthistry', [App\Http\Controllers\Invoice\InvoiceController::class, 'paymenthistry'])->name('invoice.histry');
    Route::get('apps-invoices-list' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'index'])->name('invoice');
    Route::get('vender', [App\Http\Controllers\Invoice\InvoiceController::class, 'vender'])->name('invoice.vender');
    Route::get('inventrylist', [App\Http\Controllers\Inventory\InventryController::class, 'inventry'])->name('invoice.inventrylist');
});

Route::get('{any}', [App\Http\Controllers\Home\HomeController::class, 'index'])->name('index');

//Update User Details & Auth-protected routes
Route::middleware('auth')->group(function () {
    Route::post('/update-profile/{id}', [App\Http\Controllers\Home\HomeController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-password/{id}', [App\Http\Controllers\Home\HomeController::class, 'updatePassword'])->name('updatePassword');
    Route::get('/co2quation/{id}' ,[App\Http\Controllers\Quotation\QutationController::class,'Co2quation'])->name('co2quation');
    Route::post('/co2quationstore' ,[App\Http\Controllers\Quotation\QutationController::class,'Co2quationstore'])->name('Co2quationstore');

    Route::get('apps-invoices-list/data' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'listData'])->name('invoice.data');
    Route::get('admin/invoice/getproductvalue' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'getproduct'])->name('invoice.product');
    Route::get('admin/invoice/getproduct1' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'getproductvalue1'])->name('invoice.product1');
    Route::post('update-payment', [App\Http\Controllers\Invoice\InvoiceController::class, 'updatePayment']);
    Route::post('invoicestore' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'store'])->name('invoice.store');
    Route::get('invoiceddetails/{id}' ,[App\Http\Controllers\Invoice\InvoiceController::class, 'details'])->name('invoice.details');

    Route::post('inventrystore', [App\Http\Controllers\Inventory\InventryController::class, 'inventrystore'])->name('inventrystore');
    Route::post('quantityupdate', [App\Http\Controllers\Inventory\InventryController::class, 'quantityupdate'])->name('quantityupdate');

    Route::get('product' ,[App\Http\Controllers\Product\ProductController::class, 'index'])->name('product');
    Route::post('productstore' ,[App\Http\Controllers\Product\ProductController::class, 'productstore'])->name('productstore');
    Route::get('productdelete/{id}' ,[App\Http\Controllers\Product\ProductController::class, 'delete'])->name('product.delete');

    Route::get('standerconfig/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'standerconfig'])->name('standerconfig');
    Route::get('TechnicalParameters/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'technicalparameters'])->name('TechnicalParameters');
    Route::get('standerconfiglist/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'standerconfiglist'])->name('standerconfiglist');

    Route::get('softerwere/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softerwere'])->name('softerwere');
    Route::post('softerwere/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softerwerestore'])->name('softerwerestore');
    Route::post('cutting/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingstore'])->name('cuttingstore');
    Route::post('focusing/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'focusingstore'])->name('focusingstore');
    Route::post('power/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'powerstore'])->name('powerstore');
    Route::get('softerwere/show' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softerwereshow'])->name('softerwere.show');

    Route::post('cuttingway/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingwaystore'])->name('cuttingwaystore');
    Route::post('cncthinkness/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cncthinknessstore'])->name('cncthinknessstore');
    Route::get('cuttingway/{id}' ,[App\Http\Controllers\Product\ProductConfigController::class, 'cuttingway'])->name('cuttingway');

    Route::post('motor/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'motorstore'])->name('motorstore');
    Route::post('gear/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'gearstore'])->name('gearstore');
    Route::post('rack/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'rackstore'])->name('rackstore');
    Route::post('Software/store' ,[App\Http\Controllers\Product\ProductConfigController::class, 'softwarestore'])->name('softwarestore');

    Route::get('fiberqutation/{id}' ,[App\Http\Controllers\Quotation\QutationController::class, 'generatequtation'])->name('generatequtation');
    Route::post('fiberqutation/store' ,[App\Http\Controllers\Quotation\QutationController::class, 'generatequtationstore'])->name('generatequtationstore');
    Route::get('admin/listqutation' ,[App\Http\Controllers\Quotation\QutationController::class, 'index'])->name('listqutation');
    Route::get('/printquation/{id}', [App\Http\Controllers\Quotation\QutationController::class, 'print'])->name('quation.pdf');
});


