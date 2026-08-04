<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class ReCaptcha implements Rule
{
    public function passes($attribute, $value)
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $value,
        ])->json();

        return isset($response['success']) && $response['success'] && $response['score'] >= 0.5;
    }

    public function message()
    {
        return 'reCAPTCHA verification failed. Please try again.';
    }
}
