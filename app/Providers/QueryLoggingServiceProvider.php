<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryLoggingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Log slow queries (queries taking more than 100ms)
        if (config('app.debug')) {
            DB::listen(function ($query) {
                $time = $query->time;

                // Log slow queries
                if ($time > 100) {
                    Log::channel('slow_queries')->debug('Slow query detected:', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $time . 'ms',
                        'connection' => $query->connectionName,
                    ]);
                }

                // Log all queries in debug mode
                if ($time > 50) {
                    Log::debug('Query executed:', [
                        'sql' => $query->sql,
                        'time' => $time . 'ms',
                        'connection' => $query->connectionName,
                    ]);
                }
            });
        }
    }
}
