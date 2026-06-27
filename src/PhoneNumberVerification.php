<?php

namespace Manoar\FirebasePnv;

/**
 * Entry point for the Firebase Phone Number Verification plugin.
 *
 * Resolve via the FirebasePNV facade:
 *
 *   use Manoar\FirebasePnv\Facades\FirebasePNV;
 *
 *   // Kick off verification (result arrives via the Verified event):
 *   FirebasePNV::verify();
 *
 *   // ...or fluently:
 *   FirebasePNV::verify()
 *       ->id('checkout-42')
 *       ->test()                 // SIM-less test mode (uses config token)
 *       ->dispatch();
 *
 *   // Check device capability first (result via SupportInfoRetrieved):
 *   FirebasePNV::supportInfo()->check();
 */
class PhoneNumberVerification
{
    /**
     * Begin a phone-number verification flow.
     */
    public function verify(): PendingVerification
    {
        return new PendingVerification;
    }

    /**
     * Check whether the device/SIM(s) support Firebase PNV before prompting.
     */
    public function supportInfo(): PendingSupportInfo
    {
        return new PendingSupportInfo;
    }
}
