<?php

namespace Manoar\FirebasePnv\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when Firebase PNV successfully verifies the device's phone number.
 *
 * The native layer dispatches this event by class name via
 * POST /_native/api/events, where the JSON payload keys are spread as
 * *named* constructor arguments (`new Verified(...$payload)`). The property
 * names below MUST match the payload keys emitted by the Kotlin bridge
 * (see resources/android/src/.../FirebasePnvFunctions.kt).
 *
 * Security note: trust `$token`, not `$phoneNumber`. Send the signed token to
 * your backend and verify it against Firebase before granting any access. The
 * raw phone number is for display/UX only.
 */
class Verified
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $phoneNumber,
        public string $token,
        public ?string $id = null,
    ) {}
}
