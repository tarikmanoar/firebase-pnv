# Changelog

All notable changes to `tarikmanoar/firebase-pnv` will be documented here.

## 1.0.2 - 2026-06-27

### Added
- README: status badges (Packagist version, downloads, license) and a Support
  section (GitHub Issues / Discussions / email) for the marketplace listing.

## 1.0.1 - 2026-06-27

### Fixed
- Declare `nativephp/mobile` (`^2.6 || ^3.0`) as a runtime dependency in
  `composer.json` `require`. The plugin contributes bridge functions to the
  NativePHP Mobile SDK, so this is a hard dependency (was previously only in
  `suggest`). Resolves the marketplace "Requires nativephp/mobile SDK" check.

## 1.0.0 - 2026-06-27

### Added
- Initial release: NativePHP Mobile plugin wrapping the Firebase Phone Number
  Verification (PNV) Android SDK (`com.google.firebase:firebase-pnv`).
- `FirebasePNV::verify()` — full verification flow via Android Credential Manager
  (`getVerifiedPhoneNumber`).
- `FirebasePNV::supportInfo()` — device/SIM capability check
  (`getVerificationSupportInfo`).
- Fluent `PendingVerification` / `PendingSupportInfo` builders (`id`, `test`,
  `event`, `failureEvent`, `remember`, `dispatch`/`check`).
- Asynchronous results delivered as Laravel events: `Verified`,
  `SupportInfoRetrieved`, `VerificationFailed`.
- Kotlin bridge (`FirebasePnvFunctions`) conforming to the NativePHP `BridgeFunction`
  contract, dispatching results back to PHP via `NativeActionCoordinator.dispatchEvent`.
- SIM-less test-session support via `FIREBASE_PNV_TEST_TOKEN`.
- JavaScript helper (`resources/js/firebase-pnv.js`).
- iOS unsupported-platform stub (Firebase PNV is Android-only).
