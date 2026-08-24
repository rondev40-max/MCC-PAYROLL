import java.util.Properties

// Versions come from the root build.gradle.kts, which declares these with
// `apply false`. Repeating a version here is what broke the build.
plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

// ── Release signing credentials ──────────────────────────────────────────────
//
// keystore.properties and the .jks it names are gitignored — key material never
// goes into version control. Back both up: Android refuses an update signed by
// a different key, so losing the key means never shipping an update to an
// already-installed app again.
//
// An unsigned APK is not installable. Android rejects it before the package
// manager ever sees it, with nothing more helpful than "app not installed".
// This build used to fall through to producing app-release-unsigned.apk
// whenever the credentials were missing, so that only surfaced on someone's
// phone. checkReleaseSigning below turns it into a build failure that says
// what to do.
val keystorePropertiesFile: File = rootProject.file("keystore.properties")

val signingCredentials: Properties? = keystorePropertiesFile
    .takeIf(File::exists)
    ?.let { file -> Properties().apply { file.inputStream().use(::load) } }

/**
 * Where the keystore actually lives.
 *
 * Resolved against the project root, which is where keystore.properties sits. A
 * bare file() here resolves relative to app/ instead, so the storeFile that
 * keystore.properties.example documents would never have been found.
 */
val signingStoreFile: File? = signingCredentials
    ?.getProperty("storeFile")
    ?.takeIf(String::isNotBlank)
    ?.let(rootProject::file)

android {
    namespace = "com.mcc.payroll"
    compileSdk = 34

    defaultConfig {
        applicationId = "com.mcc.payroll"
        minSdk = 24
        targetSdk = 34
        versionCode = 1
        versionName = "1.0.0"

        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"
        vectorDrawables { useSupportLibrary = true }
    }


    signingConfigs {
        create("release") {
            signingCredentials?.let { creds ->
                storeFile = signingStoreFile
                storePassword = creds.getProperty("storePassword")
                keyAlias = creds.getProperty("keyAlias")
                keyPassword = creds.getProperty("keyPassword")

                // v2 arrived in Android 7.0, which is exactly this app's
                // minSdk (24) — so every device that can install this build
                // verifies v2, and the old v1 JAR signing is redundant. AGP
                // drops v1 by itself at minSdk >= 24; stating it here keeps
                // the intent explicit if minSdk is ever lowered.
                enableV1Signing = false
                enableV2Signing = true
                enableV3Signing = true
            }
        }
    }
    buildTypes {
        debug {
            // Points at the Laravel dev server as seen from the Android emulator:
            // 10.0.2.2 is the host machine's loopback. On a physical device this
            // needs your LAN address instead (e.g. http://192.168.100.43:8000/api/).
            buildConfigField("String", "API_BASE_URL", "\"http://10.0.2.2:8000/api/\"")
            // Cleartext is debug-only; release traffic must be HTTPS.
            manifestPlaceholders["usesCleartextTraffic"] = "true"
        }
        release {
            signingConfig = signingConfigs.getByName("release")
            // The doubled /api/api/ is deliberate — do not "tidy" it.
            //
            // Laravel registers routes/api.php under an `api/` prefix, and on
            // Vercel the PHP function lives at api/index.php, whose own `/api`
            // path segment Vercel strips before Laravel sees the request. So a
            // call to /api/mobile/login reaches Laravel as `mobile/login` and
            // 404s: the app installed fine and then could never sign in. The
            // routes are genuinely published at /api/api/ on this deployment —
            // /api/api/health returns 200 and /api/api/mobile/login validates.
            //
            // If the Vercel routing is ever fixed to serve them at /api/, this
            // string has to change with it, in a new versionCode.
            buildConfigField("String", "API_BASE_URL", "\"https://mcc-payroll-abfm-pi.vercel.app/api/api/\"")
            manifestPlaceholders["usesCleartextTraffic"] = "false"
            isMinifyEnabled = true
            isShrinkResources = true
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }
    kotlinOptions {
        jvmTarget = "17"
    }
    buildFeatures {
        compose = true
        buildConfig = true
    }
    composeOptions {
        kotlinCompilerExtensionVersion = "1.5.8"
    }
    packaging {
        resources { excludes += "/META-INF/{AL2.0,LGPL2.1}" }
    }
}

dependencies {
    implementation("androidx.core:core-ktx:1.12.0")
    implementation("androidx.lifecycle:lifecycle-runtime-ktx:2.7.0")
    implementation("androidx.lifecycle:lifecycle-runtime-compose:2.7.0")
    implementation("androidx.activity:activity-compose:1.8.2")

    implementation(platform("androidx.compose:compose-bom:2024.01.00"))
    implementation("androidx.compose.ui:ui")
    implementation("androidx.compose.ui:ui-graphics")
    implementation("androidx.compose.ui:ui-tooling-preview")
    implementation("androidx.compose.material3:material3")
    // Pull-to-refresh only. Material3 1.1.2 (compose-bom 2024.01.00) has no
    // PullToRefreshBox — that lands in 1.3 — and the Material 2 modifier
    // composes fine inside Material 3 surfaces.
    implementation("androidx.compose.material:material")
    // The default icon set has no Receipt/Logout/Badge glyphs this app needs.
    implementation("androidx.compose.material:material-icons-extended")
    implementation("androidx.lifecycle:lifecycle-viewmodel-compose:2.7.0")
    implementation("androidx.navigation:navigation-compose:2.7.6")

    implementation("com.squareup.retrofit2:retrofit:2.9.0")
    implementation("com.squareup.retrofit2:converter-gson:2.9.0")
    implementation("com.squareup.okhttp3:okhttp:4.12.0")
    implementation("com.squareup.okhttp3:logging-interceptor:4.12.0")

    implementation("androidx.datastore:datastore-preferences:1.0.0")
    implementation("org.jetbrains.kotlinx:kotlinx-coroutines-android:1.7.3")

    testImplementation("junit:junit:4.13.2")
    androidTestImplementation("androidx.test.ext:junit:1.1.5")
    androidTestImplementation("androidx.test.espresso:espresso-core:3.5.1")
    androidTestImplementation(platform("androidx.compose:compose-bom:2024.01.00"))
    androidTestImplementation("androidx.compose.ui:ui-test-junit4")
    debugImplementation("androidx.compose.ui:ui-tooling")
    debugImplementation("androidx.compose.ui:ui-test-manifest")
}

/**
 * Refuse to package an unsigned release.
 *
 * Everything here is checked before a single class is packaged, so a release
 * build stops in seconds with a message you can act on, instead of spending
 * four minutes producing an APK that every device will reject.
 */
val checkReleaseSigning by tasks.registering {
    group = "verification"
    description = "Verifies the release signing key is present and usable."

    doFirst {
        val problems = buildList {
            if (signingCredentials == null) {
                add("$keystorePropertiesFile is missing - copy keystore.properties.example to it and fill it in.")
            } else {
                listOf("storeFile", "storePassword", "keyAlias", "keyPassword")
                    .filter { signingCredentials.getProperty(it).isNullOrBlank() }
                    .forEach { add("keystore.properties is missing a value for '$it'.") }

                signingCredentials.getProperty("storePassword")
                    ?.takeIf { it == "CHANGE_ME" }
                    ?.let { add("keystore.properties still has the example CHANGE_ME passwords in it.") }

                if (signingStoreFile?.exists() == false) {
                    add("Keystore file not found at $signingStoreFile - create it with keytool (see keystore.properties.example).")
                }
            }
        }

        if (problems.isNotEmpty()) {
            throw GradleException(
                buildString {
                    appendLine("Cannot build a release APK: the signing key is not set up.")
                    appendLine("An unsigned APK is not installable - Android rejects it outright.")
                    appendLine()
                    problems.forEach { appendLine("  - $it") }
                }
            )
        }
    }
}

tasks.matching { it.name == "packageRelease" }.configureEach {
    dependsOn(checkReleaseSigning)
}

/**
 * Copy the signed release APK to where the website serves it.
 *
 * The landing page's "Download APK Directly" button and its QR code both point
 * at public/downloads/mcc-employee-app.apk. That path held a 486-byte text file
 * explaining how to build and copy the APK by hand — so every visitor who
 * scanned the QR code downloaded instructions and got "app not installed".
 * Making it a build step means the download cannot drift out of sync with the
 * app again.
 */
val publishApk by tasks.registering(Copy::class) {
    group = "distribution"
    description = "Builds the signed release APK and copies it to public/downloads/ for the website."

    dependsOn("assembleRelease")

    from(layout.buildDirectory.file("outputs/apk/release/app-release.apk"))
    into(rootProject.file("../public/downloads"))
    rename { "mcc-employee-app.apk" }

    doLast {
        logger.lifecycle("Published mcc-employee-app.apk (versionName ${android.defaultConfig.versionName}, versionCode ${android.defaultConfig.versionCode})")
    }
}
