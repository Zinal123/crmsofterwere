<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Relations\Relation;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
         // Check if the sessions directory exists; if not, create it
    if (!File::isDirectory(storage_path('framework/sessions'))) {
        File::makeDirectory(storage_path('framework/sessions'), 0755, true);
    }

        Relation::morphMap([
            'employee' => \App\Models\Employee::class,
            'employee_document' => \App\Models\EmployeeDocument::class,
            'attendance' => \App\Models\Attendance::class,
            'salary_payment' => \App\Models\SalaryPayment::class,
            'invoice' => \App\Models\Invoice::class,
            'customer' => \App\Models\Customer::class,
            'invoiceproduct' => \App\Models\Invoiceproduct::class,
            'paidamount' => \App\Models\Paidamount::class,
            'product' => \App\Models\Product::class,
            'softerwere' => \App\Models\Softerwere::class,
            'softerwere1' => \App\Models\Softerwere1::class,
            'lasercutting' => \App\Models\Lasercutting::class,
            'fource' => \App\Models\Fource::class,
            'power' => \App\Models\Power::class,
            'motor' => \App\Models\Motor::class,
            'gear' => \App\Models\Gear::class,
            'rack' => \App\Models\Rack::class,
            'cutting' => \App\Models\Cutting::class,
            'cnsthinks' => \App\Models\Cnsthinks::class,
            'termandcondition' => \App\Models\Termandcondition::class,
            'quotation' => \App\Models\Quation::class,
            'inventory' => \App\Models\Invetry::class,
            'user' => \App\Models\User::class,
            'machine' => \App\Models\Machine::class,
            'bank' => \App\Models\Bank::class,
        ]);
    }
}
