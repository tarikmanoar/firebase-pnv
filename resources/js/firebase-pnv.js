/**
 * Firebase Phone Number Verification — optional JavaScript helper.
 *
 * The PHP facade (Manoar\FirebasePnv\Facades\FirebasePNV) is the primary API.
 * This stub is for apps that prefer to drive the flow from the front-end. It
 * uses the same NativePHP bridge endpoints the PHP layer uses:
 *
 *   - POST /_native/api/call   to invoke a native bridge function
 *   - "native-event" DOM event delivers async results (also Livewire "native:*")
 *
 * Results arrive asynchronously via events, never as the call's return value:
 *   - Manoar\FirebasePnv\Events\Verified            { phoneNumber, token, id }
 *   - Manoar\FirebasePnv\Events\SupportInfoRetrieved{ supported, sims, id }
 *   - Manoar\FirebasePnv\Events\VerificationFailed  { code, message, id }
 */

const VERIFIED_EVENT = 'Manoar\\FirebasePnv\\Events\\Verified';
const SUPPORT_EVENT = 'Manoar\\FirebasePnv\\Events\\SupportInfoRetrieved';
const FAILED_EVENT = 'Manoar\\FirebasePnv\\Events\\VerificationFailed';

async function call(method, params = {}) {
    const response = await fetch('/_native/api/call', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ method, params }),
    });

    return response.json();
}

function on(eventName, handler) {
    const listener = (e) => {
        if (e.detail && e.detail.event === eventName) {
            handler(e.detail.payload || {});
        }
    };
    document.addEventListener('native-event', listener);

    // Return an unsubscribe function.
    return () => document.removeEventListener('native-event', listener);
}

export const FirebasePNV = {
    /**
     * Start the verification flow. Listen with onVerified()/onFailed().
     * @param {{id?: string, testToken?: string}} options
     */
    verify(options = {}) {
        return call('FirebasePnv.GetVerifiedPhoneNumber', options);
    },

    /**
     * Check device/SIM support. Listen with onSupportInfo().
     * @param {{id?: string, testToken?: string}} options
     */
    supportInfo(options = {}) {
        return call('FirebasePnv.GetVerificationSupportInfo', options);
    },

    /** @param {(payload: {phoneNumber: string, token: string, id?: string}) => void} handler */
    onVerified(handler) {
        return on(VERIFIED_EVENT, handler);
    },

    /** @param {(payload: {supported: boolean, sims: Array, id?: string}) => void} handler */
    onSupportInfo(handler) {
        return on(SUPPORT_EVENT, handler);
    },

    /** @param {(payload: {code: string, message: string, id?: string}) => void} handler */
    onFailed(handler) {
        return on(FAILED_EVENT, handler);
    },
};

export default FirebasePNV;
