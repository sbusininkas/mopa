<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\School;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share active school across all views
        View::composer('*', function ($view) {
            $active = null;
            if (session()->has('active_school_id')) {
                $active = School::find(session('active_school_id'));
            }
            $view->with('activeSchool', $active);
        });
        //
    }
}
