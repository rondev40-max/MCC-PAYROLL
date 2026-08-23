<?php

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This host sits behind LiteSpeed, which terminates TLS and forwards to PHP
 * over plain HTTP from the loopback address.
 *
 * With no proxy trusted, $request->secure() was false on every HTTPS request,
 * so ForceHttps redirected all of them — and the redirected request looked
 * identical, so it redirected again. Page loads survived on the LiteSpeed
 * cache; the attendance register's fetch() looped until the browser gave up.
 */
function forwardedRequest(string $remoteAddr, string $clientIp = '124.217.0.178'): Request
{
    $request = Request::create('http://mccdigitalpayroll.com/attendance/api/attendance-data/bsit', 'GET');
    $request->headers->set('X-Forwarded-Proto', 'https');
    $request->headers->set('X-Forwarded-For', $clientIp);
    $request->server->set('REMOTE_ADDR', $remoteAddr);

    app(TrustProxies::class)->handle($request, fn () => new Response());

    return $request;
}

test('a request forwarded by the local proxy is recognised as secure', function () {
    $request = forwardedRequest('127.0.0.1');

    // ForceHttps only redirects when this is false. When it was, every HTTPS
    // request redirected to HTTPS again — the loop that broke the register.
    expect($request->secure())->toBeTrue();
});

test('the real client IP survives the proxy hop', function () {
    $request = forwardedRequest('127.0.0.1');

    // Without this, throttle:5,1 pooled every visitor into one bucket keyed on
    // 127.0.0.1, so one person hitting the OTP limit locked out everyone.
    expect($request->ip())->toBe('124.217.0.178');
});

test('forged forwarding headers from the internet are ignored', function () {
    // Same headers, but arriving from a public address rather than the proxy.
    $request = forwardedRequest('203.0.113.9', clientIp: '10.0.0.1');

    expect($request->secure())->toBeFalse()
        ->and($request->ip())->toBe('203.0.113.9');
});

test('proxy trust is actually configured, not left empty', function () {
    forwardedRequest('127.0.0.1');

    expect(Request::getTrustedProxies())->not->toBeEmpty()
        ->and(Request::getTrustedProxies())->not->toContain('*');
});
