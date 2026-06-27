<?php

namespace Manoar\FirebasePnv\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a Firebase PNV support-check or verification attempt fails,
 * is cancelled by the user, or is unavailable on the device/platform.
 *
 * Payload keys must match the JSON emitted by the Kotlin/Swift bridge.
 *
 * Common `$code` values emitted by this plugin:
 *  - SUPPORT_INFO_FAILED   The getVerificationSupportInfo() call failed.
 *  - NOT_SUPPORTED         No SIM on the device supports Firebase PNV.
 *  - VERIFICATION_FAILED   getVerifiedPhoneNumber() failed or was cancelled.
 *  - UNSUPPORTED_PLATFORM  Running on a platform without Firebase PNV (e.g. iOS).
 */
class VerificationFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $code,
        public string $message,
        public ?string $id = null,
    ) {}
}
