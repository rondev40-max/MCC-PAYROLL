<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <title>@yield('title', 'Attendance Portal') - MCC Payroll</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/attendance-portal.css') }}">
    @stack('styles')
</head>
<body class="@yield('body-class')">
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <header class="portal-header no-print">
        <div class="portal-header__inner">
            <a class="portal-brand" href="{{ route('attendance.dashboard') }}" aria-label="MCC Attendance home">
                <img src="{{ asset('images/logo.png') }}" alt="MCC seal" width="40" height="40">
                <span>
                    <strong>MCC Attendance</strong>
                    <small>Personnel time records</small>
                </span>
            </a>

            <nav class="portal-nav" aria-label="Attendance portal">
                <a href="{{ route('attendance.dashboard') }}"
                   @class(['is-active' => request()->routeIs('attendance.dashboard')])
                   @if(request()->routeIs('attendance.dashboard')) aria-current="page" @endif>
                    <i class="bi bi-table" aria-hidden="true"></i>
                    <span>Register</span>
                </a>
                <a href="{{ route('attendance.dtr.index') }}"
                   @class(['is-active' => request()->routeIs('attendance.dtr.*')])
                   @if(request()->routeIs('attendance.dtr.*')) aria-current="page" @endif>
                    <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                    <span>Monthly DTR</span>
                </a>
            </nav>

            <div class="portal-account">
                <div class="portal-account__identity">
                    <span class="portal-account__name">{{ session('user_name', 'Attendance Checker') }}</span>
                    <span class="portal-account__department">{{ strtoupper(session('user_course', '')) }}</span>
                </div>
                <form method="POST" action="{{ route('attendance.logout') }}">
                    @csrf
                    <button class="icon-button icon-button--header" type="submit" title="Sign out" aria-label="Sign out">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main id="main-content" class="portal-main">
        @if(session('success'))
            <div class="flash flash--success" role="status">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="flash flash--error" role="alert">
                <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
