# Changelog

All notable changes to `tarikmanoar/firebase-pnv` will be documented here.

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
