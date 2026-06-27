<?php

namespace Manoar\FirebasePnv\Concerns;

/**
 * Thin wrapper around the NativePHP Mobile PHP->native bridge primitive.
 *
 * The host app exposes a global `nativephp_call(string $method, string $json)`
 * function (registered by nativephp/mobile via JNI). It synchronously hands the
 * request to the native bridge registry and returns a JSON string. For the
 * asynchronous Firebase PNV functions the native side returns an empty object
 * `{}` immediately; the real result is delivered later as a Laravel event
 * (see the Events/ directory) via POST /_native/api/events.
 */
trait CallsNativeBridge
{
    /**
     * Invoke a registered native bridge function.
     *
     * @return bool true if the call was handed off to the native layer without
     *              an error status; false if the bridge is unavailable, the
     *              function is not registered, or native returned an error.
     */
    protected function callNative(string $method, array $payload): bool
    {
        // Bridge is only available inside a running NativePHP Mobile shell.
        if (! function_exists('nativephp_call')) {
            return false;
        }

        // Optionally verify the method is registered before calling.
        if (function_exists('nativephp_can') && ! nativephp_can($method)) {
            return false;
        }

        $result = nativephp_call($method, json_encode((object) $payload));

        // null => function not found in the bridge registry.
        if ($result === null) {
            return false;
        }

        $decoded = json_decode($result, true);

        // A native-side error response carries an explicit "error" status.
        if (is_array($decoded) && ($decoded['status'] ?? null) === 'error') {
            return false;
        }

        return true;
    }
}
