// Reference Gradle snippet for Firebase Phone Number Verification (Android).
//
// NativePHP injects the dependency declared in `nativephp.json`
// (android.dependencies.implementation) automatically, so you normally do NOT
// need to edit Gradle by hand. This file documents the equivalent manual
// configuration and the BoM-based alternative for reference, plus the one piece
// NativePHP cannot inject for you: the google-services Gradle plugin.

// ---------------------------------------------------------------------------
// 1. Firebase PNV dependency
// ---------------------------------------------------------------------------
// Pinned version (this is what nativephp.json declares):
//
//   dependencies {
//       implementation("com.google.firebase:firebase-pnv:16.1.1")
//   }
//
// BoM alternative (recommended by Firebase — versions managed by the BoM):
//
//   dependencies {
//       implementation(platform("com.google.firebase:firebase-bom:34.15.0"))
//       implementation("com.google.firebase:firebase-pnv")
//   }

// ---------------------------------------------------------------------------
// 2. google-services plugin (REQUIRED — must be added to the host app)
// ---------------------------------------------------------------------------
// Firebase reads its configuration from `google-services.json`, processed by the
// Google Services Gradle plugin. Add Firebase to your Android project the normal
// way (https://firebase.google.com/docs/android/setup):
//
//   // settings.gradle.kts / project build.gradle.kts (plugins block)
//   plugins {
//       id("com.google.gms.google-services") version "4.4.4" apply false
//   }
//
//   // app/build.gradle.kts
//   plugins {
//       id("com.google.gms.google-services")
//   }
//
// Then drop your `app/google-services.json` into the Android app module
// (NativePHP's android project lives under the generated `nativephp/android`
// build directory after `php artisan native:run`).
