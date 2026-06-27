import Foundation

// MARK: - Firebase Phone Number Verification (iOS) — unsupported-platform stub
//
// Firebase Phone Number Verification (PNV) is an ANDROID-ONLY product; there is
// no iOS Firebase PNV SDK. These bridge functions exist so the plugin registers
// cleanly on iOS builds and returns a clear, structured error (rather than a
// "function not found"), letting the PHP layer degrade gracefully.
//
// The contract mirrors the NativePHP iOS bridge (BridgeRouter.swift):
//
//   protocol BridgeFunction {
//       func execute(parameters: [String: Any]) throws -> [String: Any]
//   }
//
//   struct BridgeResponse {
//       static func success(data: [String: Any]) -> [String: Any]
//       static func error(code: String, message: String, data: [String: Any]) -> [String: Any]
//   }
//
// Registered as: FirebasePnv.GetVerificationSupportInfo / FirebasePnv.GetVerifiedPhoneNumber

enum FirebasePnvFunctions {

    private static func unsupported() -> [String: Any] {
        BridgeResponse.error(
            code: "UNSUPPORTED_PLATFORM",
            message: "Firebase Phone Number Verification is not available on iOS."
        )
    }

    /// FirebasePnv.GetVerificationSupportInfo — iOS always reports unsupported.
    class GetVerificationSupportInfo: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            FirebasePnvFunctions.unsupported()
        }
    }

    /// FirebasePnv.GetVerifiedPhoneNumber — iOS always reports unsupported.
    class GetVerifiedPhoneNumber: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            FirebasePnvFunctions.unsupported()
        }
    }
}
