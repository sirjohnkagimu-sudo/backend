<?php

namespace App\Providers;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Artisan;
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
         Passport::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        \Validator::extend('exists_tenant', function ($attribute, $value, $parameters, $validator) {
            $model = $parameters[0]; // e.g., Category::class
            $tenantId = tenant('id');
            if (!$tenantId) {
                return false;
            }
            return $model::where('tenant_id', $tenantId)->where('id', $value)->exists();
        });




    }
}
