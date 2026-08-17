# MCC Employee App (Android / Kotlin)

Companion app for the MCC Payroll web portal. Employees sign in with the same
account they use on the site and can check their payslips, attendance record and
profile from a phone.

Built with Kotlin + Jetpack Compose (Material 3), talking to the Laravel API in
`routes/api.php`.

## Requirements

- JDK 17
- Android SDK with platform 34 installed
- `local.properties` pointing at your SDK (already generated; regenerate with
  `sdk.dir=C\:\\path\\to\\Android\\Sdk` if you move machines)

## Build

```bash
./gradlew :app:assembleDebug     # -> app/build/outputs/apk/debug/app-debug.apk
./gradlew :app:assembleRelease   # -> app/build/outputs/apk/release/app-release-unsigned.apk
./gradlew :app:lintDebug
```

## Pointing it at your server

The base URL is a `buildConfigField` in `app/build.gradle.kts`, not a constant
buried in source:

| Variant | URL | Why |
|---|---|---|
| debug | `http://10.0.2.2:8000/api/` | `10.0.2.2` is the host machine's loopback **as seen from the Android emulator**. |
| release | `https://mcc-payroll-abfm-pi.vercel.app/api/` | Production. |

Testing on a **physical device** on your Wi-Fi? `10.0.2.2` will not resolve —
change the debug URL to your PC's LAN address, e.g. `http://192.168.100.43:8000/api/`,
and serve Laravel with `php artisan serve --host=0.0.0.0`.

Cleartext HTTP is enabled in debug only (a manifest placeholder), so the release
build cannot silently fall back to an unencrypted connection.

## Architecture

```
data/remote   Retrofit service, wire models, OkHttp client + auth interceptor
data/local    SessionStore — token in DataStore, excluded from cloud backup
data/repo     EmployeeRepository — returns Outcome.Ok / Outcome.Failed(message)
viewmodel     EmployeeViewModel — one StateFlow per screen (data + loading/error)
ui/theme      Material 3 scheme, type scale and shapes derived from the web portal
ui/components Shared card, icon tile, status chip, loading / error / empty states
ui            LoginScreen, DashboardScreen, AttendanceScreen, PayslipsScreen, ProfileScreen
```

Dependencies are wired by hand in `MccApp` rather than with Hilt — five screens
do not justify the annotation processor, and the whole graph stays readable in
one file.

The bearer token is attached by `AuthInterceptor`, so no endpoint can forget it.
A 401 anywhere clears the session and returns the user to sign-in.

## Notes for whoever ships this

- **The release APK is unsigned.** Generate a keystore and configure
  `signingConfigs` before distributing, or the install will be rejected.
- `applicationId` is `com.mcc.payroll`, the same id as the Capacitor wrapper in
  `/android`. Two APKs cannot share an id on one device — retire one, or change
  this to something like `com.mcc.payroll.native`.
- The API returns Eloquent models serialised whole, so the wire models mirror the
  database columns exactly. Two are easy to get wrong: payslips use `pay_period`
  and `total_honorarium` (not `period` / `net_pay`), and announcements use
  `message` (not `content`).
