<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $username = (string) $request->input('username', 'guest');

            return Limit::perMinute(5)->by($request->ip().'|'.mb_strtolower($username));
        });

        RateLimiter::for('register', function (Request $request): Limit {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('game-session', function (Request $request): Limit {
            return Limit::perMinute(20)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('server-consume', function (Request $request): Limit {
            return Limit::perMinute(180)->by($request->ip());
        });

        RateLimiter::for('server-progress', function (Request $request): Limit {
            return Limit::perMinute(240)->by($request->ip());
        });
    }
}
