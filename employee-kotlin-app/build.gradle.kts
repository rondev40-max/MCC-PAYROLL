// Plugin versions are declared once, here, and applied without a version in
// app/build.gradle.kts.
//
// This used to be a buildscript { dependencies { classpath(...) } } block while
// the module ALSO wrote `id("com.android.application") version "8.2.2"`. Gradle
// rejects that combination outright — "the plugin is already on the classpath
// with an unknown version, so compatibility cannot be checked" — so no build
// of this project could succeed, release or debug.
//
// Kotlin 1.9.22 pairs with Compose compiler extension 1.5.8 (see
// app/build.gradle.kts). Changing one without the other fails the build with a
// version-mismatch error.
plugins {
    id("com.android.application") version "8.2.2" apply false
    id("org.jetbrains.kotlin.android") version "1.9.22" apply false
}

// Module repositories are declared in settings.gradle.kts under
// dependencyResolutionManagement (FAIL_ON_PROJECT_REPOS), so an allprojects
// repositories block here would be rejected as a duplicate declaration.

tasks.register<Delete>("clean") {
    delete(layout.buildDirectory)
}

// `wrapper` is a root-project task. This lived in app/build.gradle.kts, where
// every build failed with "Task with name 'wrapper' not found in project
// ':app'". The version tracks gradle/wrapper/gradle-wrapper.properties — it
// said 8.10.2 there while the wrapper was actually on 8.14.3, so running
// ./gradlew wrapper would have silently downgraded the build.
tasks.named<Wrapper>("wrapper") {
    gradleVersion = "8.14.3"
    distributionType = Wrapper.DistributionType.ALL
}
