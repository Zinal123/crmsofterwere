<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;


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

    }
}
