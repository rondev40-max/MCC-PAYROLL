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
        //
        // The limits used to be 5 per hour per IP and 3 per hour per email. Every
        // POST counted, including ones rejected for a typo or a weak password, so a
        // person fixing their form (or several employees registering from the same
        // school network) hit "429 Too Many Requests" almost immediately. They are
        // roomier now, and hitting them shows a message on the form, not a bare 429.
        RateLimiter::for('register', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email')));

            $tooMany = function (Request $request, array $headers) {
                $wait = (int) ($headers['Retry-After'] ?? 60);
                $minutes = max(1, (int) ceil($wait / 60));

                return redirect()->route('register.form')
                    ->withInput($request->only('name', 'email'))
                    ->with('error', "Too many registration attempts. Please wait about {$minutes} minute(s) and try again.");
            };

            return [
                Limit::perMinute(10)->by('register:ip-min:' . $request->ip())->response($tooMany),
                Limit::perHour(30)->by('register:ip:' . $request->ip())->response($tooMany),
                Limit::perHour(10)->by('register:email:' . $email)->response($tooMany),
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