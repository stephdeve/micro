<?php

namespace App\Providers;

use App\Models\Parametre;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

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
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            $locale = Session::get('locale');
        } catch (\Throwable $e) {
            $locale = null;
        }

        if (!$locale) {
            $locale = Parametre::where('cle', 'langue')->value('valeur') ?: config('app.locale');
            Session::put('locale', $locale);
        }

        App::setLocale($locale);
    }
}
