<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        // Registration is protected by both a source limit and an email limit.
        // Either one alone is cheap for an attacker to rotate.
        RateLimiter::for('register', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email')));

            return [
                Limit::perHour(5)->by('register:ip:' . $request->ip()),
                Limit::perHour(3)->by('register:email:' . $email),
            ];
        });

        RateLimiter::for('verification-resend', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email')));

            return [
                Limit::perMinute(1)->by('verification-resend:ip:' . $request->ip()),
                Limit::perHour(3)->by('verification-resend:email:' . $email),
            ];
        });

        // when the application is deployed to production we always want
        // generated URLs to use https and any plain http requests should
        // be redirected. this pairs with the ForceHttps middleware.
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Force Laravel to compile views in Vercel's writable /tmp folder
        if (config('app.env') === 'production' || isset($_ENV['VERCEL_URL'])) {
            config(['view.compiled' => '/tmp/views']);
        }

        // NOTE: there used to be a Login-event listener here that rehashed
        // $user->getAuthPassword(). That returns the stored *hash*, not the
        // plaintext, so it saved bcrypt(<old hash>) and locked the account out
        // for good. Rehash-on-login now lives in PasswordHash::checkAndUpgrade(),
        // which is the only place that actually has the submitted password.
    }
}
