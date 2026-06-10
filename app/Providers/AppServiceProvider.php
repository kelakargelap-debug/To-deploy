<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Mail;
use App\Mail\Transport\ResendTransport;

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
        Sanctum::getAccessTokenFromRequestUsing(function ($request) {
            return $request->bearerToken() ?? $request->token;
        });

        Mail::extend('resend', function (array $config = []) {
            return new ResendTransport($config['api_key'] ?? '');
        });
    }
}
