<?php

// EXAMPLE — copy into your app (e.g. app/Listeners) and register as needed.
//
// A plain Laravel listener for the Verified event. Works in any NativePHP app
// because the native layer dispatches the event server-side via
// POST /_native/api/events -> event(new Verified(...)).

namespace App\Listeners;

use Manoar\FirebasePnv\Events\Verified;

class VerifiedListener
{
    public function handle(Verified $event): void
    {
        // SECURITY: never trust $event->phoneNumber on its own. Send the signed
        // token to your backend / Firebase Admin SDK and verify it before you
        // associate the number with a user or grant access.
        //
        //   $verified = app(MyFirebaseTokenVerifier::class)->verify($event->token);
        //   if ($verified->phoneNumber === $event->phoneNumber) { ... }

        logger()->info('Phone verified', [
            'id' => $event->id,                 // correlation id from ->id()/remember()
            'phoneNumber' => $event->phoneNumber,
            'token' => substr($event->token, 0, 12).'…',
        ]);
    }
}
