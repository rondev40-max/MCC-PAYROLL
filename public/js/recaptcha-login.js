/*
 * Attaches a reCAPTCHA v3 token to a login form, then submits it.
 *
 * Shared by all three public sign-in pages (admin, attendance, employee) so the
 * hardening lives in one place. The attendance page previously had its own copy
 * with the submit listener registered inside grecaptcha.ready(), which left the
 * button stuck on "Verifying…" whenever reCAPTCHA was unconfigured, blocked, or
 * slow to answer. Nothing here may ever be the reason a form cannot submit.
 *
 * Configure per page with data attributes on the <form>:
 *   data-recaptcha-site-key="..."   omit or leave empty to skip reCAPTCHA
 *   data-recaptcha-action="login"
 *   data-recaptcha-token-field="g-recaptcha-response"
 *   data-busy-label="Signing in…"
 */
(function () {
    'use strict';

    var TOKEN_TIMEOUT_MS = 4000;

    /**
     * Resolve with a token, or null when reCAPTCHA is unavailable.
     * Never rejects, and never outlives TOKEN_TIMEOUT_MS.
     */
    function requestToken(siteKey, action) {
        if (!siteKey || typeof grecaptcha === 'undefined') {
            return Promise.resolve(null);
        }

        var token = new Promise(function (resolve) {
            try {
                grecaptcha.ready(function () {
                    grecaptcha
                        .execute(siteKey, { action: action })
                        .then(resolve)
                        .catch(function () { resolve(null); });
                });
            } catch (e) {
                resolve(null);
            }
        });

        // grecaptcha.execute() can return a promise that settles neither way,
        // so race it rather than trusting it to finish.
        var timeout = new Promise(function (resolve) {
            window.setTimeout(function () { resolve(null); }, TOKEN_TIMEOUT_MS);
        });

        return Promise.race([token, timeout]);
    }

    function setBusy(form, busy) {
        var button = form.querySelector('[type="submit"]');
        if (!button) {
            return;
        }

        var label = button.querySelector('[data-btn-text]') || button;

        if (busy) {
            button.dataset.idleLabel = label.innerHTML;
            button.disabled = true;
            label.innerHTML = form.dataset.busyLabel || 'Signing in…';
        } else {
            button.disabled = false;
            if (button.dataset.idleLabel) {
                label.innerHTML = button.dataset.idleLabel;
            }
        }
    }

    function wire(form) {
        var siteKey = form.dataset.recaptchaSiteKey || '';
        var action = form.dataset.recaptchaAction || 'login';
        var fieldName = form.dataset.recaptchaTokenField || 'g-recaptcha-response';

        form.addEventListener('submit', function (event) {
            if (form.dataset.recaptchaSubmitting === 'true') {
                return; // Already handled; let the real submit through.
            }

            event.preventDefault();

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            setBusy(form, true);

            requestToken(siteKey, action).then(function (token) {
                var field = form.querySelector('[name="' + fieldName + '"]');

                if (!field) {
                    field = document.createElement('input');
                    field.type = 'hidden';
                    field.name = fieldName;
                    form.appendChild(field);
                }

                field.value = token || '';

                // Submit past our own listener. The server is the authority on
                // whether a missing token is acceptable, not this script.
                form.dataset.recaptchaSubmitting = 'true';
                HTMLFormElement.prototype.submit.call(form);
            }).catch(function () {
                setBusy(form, false);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var forms = document.querySelectorAll('form[data-recaptcha-login]');
        Array.prototype.forEach.call(forms, wire);
    });
})();
