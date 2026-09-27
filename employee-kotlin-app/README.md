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
./gradlew :app:assembleRelease   # -> app/build/outputs/apk/release/app-release.apk  (signed)
./gradlew :app:publishApk        # assembleRelease + copy to ../public/downloads/
./gradlew :app:lintRelease
./gradlew :app:testReleaseUnitTest   # 11 JVM unit tests
```

`publishApk` is the one to run when shipping: it drops the signed APK at
`public/downloads/mcc-employee-app.apk`, which is what the landing page's
"Download APK Directly" button and QR code serve. Bump `versionCode` and
`versionName` in `app/build.gradle.kts` first — Android refuses to install an
update whose `versionCode` is not higher than the installed one.

Verify what you are about to distribute:

```bash
apksigner verify --print-certs app/build/outputs/apk/release/app-release.apk
```

"Verified using v2/v3 scheme: true" means devices will accept it. Anything
reporting the APK as unsigned will fail to install with a bare "app not
installed".

## Pointing it at your server

The base URL is a `buildConfigField` in `app/build.gradle.kts`, not a constant
buried in source:

| Variant | URL | Why |
|---|---|---|
| debug | `http://10.0.2.2:8000/api/` | `10.0.2.2` is the host machine's loopback **as seen from the Android emulator**. |
| release | `https://mcc-payroll-abfm-pi.vercel.app/api/api/` | Production. The doubled `api` is not a typo — see below. |

**Why the release URL has `/api/api/`.** Laravel registers `routes/api.php`
under an `api/` prefix. On Vercel the PHP function lives at `api/index.php`, and
Vercel strips that `/api` path segment before Laravel sees the request — so
`/api/mobile/login` arrives as `mobile/login` and 404s. The routes really are
published one level deeper on this deployment:

```bash
curl https://mcc-payroll-abfm-pi.vercel.app/api/api/health          # {"status":"ok"}
curl https://mcc-payroll-abfm-pi.vercel.app/api/mobile/login        # 404
```

If the Vercel routing is ever changed to serve them at `/api/`, this URL has to
change with it, in a new `versionCode`.

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
ui            LoginScreen, DashboardScreen, AttendanceScreen, PayslipsScreen,
              AnnouncementsScreen, ProfileScreen
```

Dependencies are wired by hand in `MccApp` rather than with Hilt — five screens
do not justify the annotation processor, and the whole graph stays readable in
one file.

The bearer token is attached by `AuthInterceptor`, so no endpoint can forget it.
A 401 anywhere clears the session and returns the user to sign-in.

## Notes for whoever ships this

- **Back up the signing key.** `mcc-release.jks` and `keystore.properties` sit
  in this directory and are gitignored, so they exist on one machine only. If
  they are lost you can never ship an update to an installed app — Android
  refuses an APK signed by a different key, and every user would have to
  uninstall and reinstall, losing their session. Copy both somewhere safe now.
  The certificate currently in use (created 2026-09-28 for version 1.0.1):

  ```
  CN=MCC Payroll, OU=MIS, O=Madridejos Community College, L=Madridejos, ST=Cebu, C=PH
  SHA-256  ed:c5:8a:0e:ed:3c:6f:0e:95:93:e9:90:93:1f:22:e1:df:5c:72:a9:52:76:21:04:2c:1c:dd:6d:f6:d1:12:a1
  ```

  This already happened once. Version 1.0.0 was signed with a key
  (`SHA-256 b2:37:59:9c:…:e8:46:e7`) that was never backed up and was lost
  when the project moved machines, so 1.0.1 had to be signed with a new
  one. Anyone who installed 1.0.0 has to uninstall it before 1.0.1 will
  install — the download dialog on the landing page says so.
- **Keep material3 and the Compose core libraries from the same BOM
  generation.** compose-bom 2024.01.00 mixed material3 1.1.2 with
  animation-core 1.6.0; R8 then stripped a compatibility overload material3
  needed, and every release APK crashed on launch with
  `NoSuchMethodError: ... KeyframesSpec$KeyframeEntity`. Debug builds skip
  R8, so this only ever shows in a release build — install the release APK on
  a device or emulator before publishing it.

  A release build now *fails* if the key is missing, rather than quietly
  emitting an unsigned APK that no device will install — see
  `checkReleaseSigning` in `app/build.gradle.kts`.
- `applicationId` is `com.mcc.payroll`. The Capacitor wrapper in `/android` uses
  `com.example.payroll`, so the two no longer collide — but that wrapper is
  unfinished (its manifest names a launcher activity, `.ui.login.LoginActivity`,
  that has no source file) and should be retired rather than built.
- The API returns Eloquent models serialised whole, so the wire models mirror the
  database columns exactly. Two are easy to get wrong: payslips use `pay_period`
  and `total_honorarium` (not `period` / `net_pay`), and announcements use
  `message` (not `content`).
