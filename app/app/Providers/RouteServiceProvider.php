<?php

namespace App\Providers;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
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

        RateLimiter::for('api', function (Request $request) {
            $limit_per_minute_api=env('LIMIT_PER_MINUTE_API');
            return Limit::perMinute($limit_per_minute_api)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
