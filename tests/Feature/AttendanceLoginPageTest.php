<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Guards the attendance sign-in page against the regression where the button
 * stuck on "Verifying…" and never submitted.
 *
 * The cause was structural: the submit listener was registered inside
 * grecaptcha.ready(), so an unconfigured, blocked or slow reCAPTCHA left the
 * form with no way to submit at all. These tests pin the two properties that
 * make that impossible rather than asserting on exact markup.
 */

test('login page does not load reCAPTCHA when no site key is configured', function () {
    config(['services.recaptcha.site_key' => null]);

    $response = $this->get(route('attendance.attendlog.form'));

    $response->assertOk();
    // An empty render= parameter yields a grecaptcha that never becomes ready.
    $response->assertDontSee('recaptcha/api.js', false);
});

test('login page loads reCAPTCHA when a site key is configured', function () {
    config(['services.recaptcha.site_key' => 'test-site-key']);

    $response = $this->get(route('attendance.attendlog.form'));

    $response->assertOk();
    $response->assertSee('recaptcha/api.js?render=test-site-key', false);
});

test('the page opts into the shared submit handler', function () {
    config(['services.recaptcha.site_key' => null]);

    $html = $this->get(route('attendance.attendlog.form'))->getContent();

    // Submit handling moved to a shared script so admin, attendance and
    // employee sign-in all behave the same way.
    expect($html)->toContain('js/recaptcha-login.js')
        ->and($html)->toContain('data-recaptcha-login');
});

test('the shared submit handler cannot be blocked by reCAPTCHA', function () {
    $js = file_get_contents(public_path('js/recaptcha-login.js'));

    // The listener attaches on DOMContentLoaded, never from inside a
    // grecaptcha.ready() callback that may never fire.
    expect($js)->toContain("document.addEventListener('DOMContentLoaded'");

    // Every reCAPTCHA call sits behind an availability guard.
    expect($js)->toContain("typeof grecaptcha === 'undefined'");

    // And the token request races a timeout so it cannot hang.
    expect($js)->toContain('Promise.race');
});

test('every public login form uses the shared handler', function () {
    $forms = [
        route('attendance.attendlog.form'),
        route('admin.login.form'),
        route('employee.login.form'),
    ];

    foreach ($forms as $url) {
        $html = $this->get($url)->getContent();

        expect($html)->toContain('data-recaptcha-login')
            ->and($html)->toContain('js/recaptcha-login.js');
    }
});

test('the form still posts to the attendance login route', function () {
    $html = $this->get(route('attendance.attendlog.form'))->getContent();

    expect($html)->toContain(route('attendance.attendlog'))
        ->and($html)->toContain('id="attendance-login-form"');
});
