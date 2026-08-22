import java.util.Properties

plugins {
    id("com.android.application")
    id("kotlin-android")
}

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
            val props = rootProject.file("keystore.properties")
            if (props.exists()) {
                val creds = Properties().apply { load(props.inputStream()) }
                storeFile = file(creds.getProperty("storeFile"))
                storePassword = creds.getProperty("storePassword")
                keyAlias = creds.getProperty("keyAlias")
                keyPassword = creds.getProperty("keyPassword")
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
            if (rootProject.file("keystore.properties").exists()) {
                signingConfig = signingConfigs.getByName("release")
            }
            buildConfigField("String", "API_BASE_URL", "\"https://mcc-payroll-abfm-pi.vercel.app/api/\"")
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
