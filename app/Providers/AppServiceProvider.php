<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Login;
use App\Models\User;

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

        Event::listen(Login::class, function (Login $event) {
            /** @var User $user */
            $user = $event->user;

            if (Hash::needsRehash($user->getAuthPassword())) {
                $user->password = Hash::make($user->getAuthPassword());
                $user->save();
            }
        });
    }
}
