<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('layouts.topbar', function ($view) {
            $user = auth()->user();

            $view->with('jobNotifications', $user
                ? $user->notifications()->latest()->limit(10)->get()
                : collect());
            $view->with('unreadJobNotificationCount', $user ? $user->unreadNotifications()->count() : 0);
        });
    }
}
