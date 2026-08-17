buildscript {
    repositories {
        google()
        mavenCentral()
    }
    dependencies {
        // Kotlin 1.9.22 pairs with Compose compiler extension 1.5.8 (see app/build.gradle.kts).
        // Changing one without the other fails the build with a version-mismatch error.
        classpath("com.android.tools.build:gradle:8.2.2")
        classpath("org.jetbrains.kotlin:kotlin-gradle-plugin:1.9.22")
    }
}

// Module repositories are declared in settings.gradle.kts under
// dependencyResolutionManagement (FAIL_ON_PROJECT_REPOS), so an allprojects
// repositories block here would be rejected as a duplicate declaration.

tasks.register<Delete>("clean") {
    delete(rootProject.buildDir)
}
