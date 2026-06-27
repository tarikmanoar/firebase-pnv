package com.tarikmanoar.plugins.firebasepnv

import android.os.Handler
import android.os.Looper
import android.util.Log
import androidx.fragment.app.FragmentActivity
import com.google.firebase.pnv.FirebasePhoneNumberVerification
import com.nativephp.mobile.bridge.BridgeFunction
import com.nativephp.mobile.utils.NativeActionCoordinator
import org.json.JSONArray
import org.json.JSONObject

/**
 * NativePHP <-> Firebase Phone Number Verification (PNV) bridge.
 *
 * Namespace: "FirebasePnv.*" (registered in BridgeFunctionRegistration).
 *
 * Both functions are ASYNCHRONOUS. `execute()` hands the work off and returns an
 * empty map immediately; the real outcome is delivered back to the PHP/Laravel
 * layer later by dispatching a named Laravel event through
 * `NativeActionCoordinator.dispatchEvent(activity, event, payloadJson)`. That
 * helper runs JS in the WebView which POSTs to `/_native/api/events`, where
 * NativePHP does `new $event(...$payload)` and `event($event)`.
 *
 * Because the payload is spread as *named* constructor arguments, every JSON key
 * below MUST exactly match a constructor parameter of the matching PHP event in
 * `Manoar\FirebasePnv\Events`:
 *   - Verified            { phoneNumber, token, id }
 *   - SupportInfoRetrieved{ supported, sims, id }
 *   - VerificationFailed  { code, message, id }
 *
 * @see <a href="https://firebase.google.com/docs/phone-number-verification/android/get-started">Firebase PNV — Get started (Android)</a>
 */
object FirebasePnvFunctions {

    private const val TAG = "FirebasePnv"

    // Default PHP event classes. The PHP layer can override these per-call by
    // passing "event" / "failEvent" parameters.
    private const val DEFAULT_VERIFIED = "Manoar\\FirebasePnv\\Events\\Verified"
    private const val DEFAULT_SUPPORT = "Manoar\\FirebasePnv\\Events\\SupportInfoRetrieved"
    private const val DEFAULT_FAILED = "Manoar\\FirebasePnv\\Events\\VerificationFailed"

    /**
     * Resolve the Firebase PNV singleton, enabling SIM-less test mode when a
     * console-issued test token is supplied by the PHP layer.
     */
    private fun instance(testToken: String?): FirebasePhoneNumberVerification {
        val fpnv = FirebasePhoneNumberVerification.getInstance()
        if (!testToken.isNullOrEmpty()) {
            fpnv.enableTestSession(testToken)
        }
        return fpnv
    }

    private fun dispatchFailure(
        activity: FragmentActivity,
        event: String,
        id: String?,
        code: String,
        message: String?,
    ) {
        val payload = JSONObject().apply {
            put("code", code)
            put("message", message ?: "Unknown error")
            if (id != null) put("id", id)
        }
        NativeActionCoordinator.dispatchEvent(activity, event, payload.toString())
    }

    /**
     * FirebasePnv.GetVerificationSupportInfo
     *
     * Parameters (from PHP): id?, event?, failEvent?, testToken?
     * Mirrors Firebase PNV `getVerificationSupportInfo()`.
     */
    class GetVerificationSupportInfo(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val id = parameters["id"] as? String
            val event = parameters["event"] as? String ?: DEFAULT_SUPPORT
            val failEvent = parameters["failEvent"] as? String ?: DEFAULT_FAILED
            val testToken = parameters["testToken"] as? String

            Log.d(TAG, "🔎 getVerificationSupportInfo (id=$id)")

            Handler(Looper.getMainLooper()).post {
                try {
                    instance(testToken).getVerificationSupportInfo()
                        .addOnSuccessListener { results ->
                            val sims = JSONArray()
                            var anySupported = false

                            results.forEachIndexed { index, info ->
                                val supported = info.isSupported()
                                if (supported) anySupported = true
                                sims.put(
                                    JSONObject().apply {
                                        put("index", index)
                                        put("supported", supported)
                                    }
                                )
                            }

                            val payload = JSONObject().apply {
                                put("supported", anySupported)
                                put("sims", sims)
                                if (id != null) put("id", id)
                            }

                            Log.d(TAG, "✅ support info: supported=$anySupported, sims=${results.size}")
                            NativeActionCoordinator.dispatchEvent(activity, event, payload.toString())
                        }
                        .addOnFailureListener { e ->
                            Log.e(TAG, "❌ getVerificationSupportInfo failed: ${e.message}", e)
                            dispatchFailure(activity, failEvent, id, "SUPPORT_INFO_FAILED", e.message)
                        }
                } catch (e: Exception) {
                    Log.e(TAG, "❌ getVerificationSupportInfo threw: ${e.message}", e)
                    dispatchFailure(activity, failEvent, id, "SUPPORT_INFO_FAILED", e.message)
                }
            }

            // Async — the result is delivered via the event above.
            return emptyMap()
        }
    }

    /**
     * FirebasePnv.GetVerifiedPhoneNumber
     *
     * Parameters (from PHP): id?, event?, failEvent?, testToken?
     * Runs the full Firebase PNV flow: it invokes the Android Credential Manager
     * to obtain user consent to share the carrier phone number, calls the
     * Firebase PNV backend, and returns a signed token via `getToken()`.
     */
    class GetVerifiedPhoneNumber(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val id = parameters["id"] as? String
            val event = parameters["event"] as? String ?: DEFAULT_VERIFIED
            val failEvent = parameters["failEvent"] as? String ?: DEFAULT_FAILED
            val testToken = parameters["testToken"] as? String

            Log.d(TAG, "📞 getVerifiedPhoneNumber (id=$id)")

            // Credential Manager presents UI and requires an Activity on the UI thread.
            Handler(Looper.getMainLooper()).post {
                try {
                    instance(testToken).getVerifiedPhoneNumber(activity)
                        .addOnSuccessListener { result ->
                            val payload = JSONObject().apply {
                                put("phoneNumber", result.getPhoneNumber() ?: "")
                                put("token", result.getToken() ?: "")
                                if (id != null) put("id", id)
                            }

                            Log.d(TAG, "✅ phone number verified")
                            NativeActionCoordinator.dispatchEvent(activity, event, payload.toString())
                        }
                        .addOnFailureListener { e ->
                            Log.e(TAG, "❌ getVerifiedPhoneNumber failed: ${e.message}", e)
                            dispatchFailure(activity, failEvent, id, "VERIFICATION_FAILED", e.message)
                        }
                } catch (e: Exception) {
                    Log.e(TAG, "❌ getVerifiedPhoneNumber threw: ${e.message}", e)
                    dispatchFailure(activity, failEvent, id, "VERIFICATION_FAILED", e.message)
                }
            }

            // Async — the result is delivered via the event above.
            return emptyMap()
        }
    }
}
