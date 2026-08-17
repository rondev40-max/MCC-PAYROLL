<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
     * reCAPTCHA v3. When either key is missing the rule is not applied at all
     * (see App\Rules\ReCaptcha::isConfigured), so local development works
     * without keys and production is protected once they are set.
     */
    'recaptcha' => [
        'site_key'   => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),

        // Tokens scoring below this are treated as automated. 0.5 is Google's
        // suggested starting point; raise it if you still see bot sign-ups.
        'min_score'  => env('RECAPTCHA_MIN_SCORE', 0.5),

        // Seconds to wait for siteverify before giving up.
        'timeout'    => env('RECAPTCHA_TIMEOUT', 5),

        // If Google is unreachable, allow the sign-in rather than locking every
        // employee out of payroll. Set to false to be strict instead.
        'fail_open'  => env('RECAPTCHA_FAIL_OPEN', true),
    ],


];
