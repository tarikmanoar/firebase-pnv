<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Test Session Token
    |--------------------------------------------------------------------------
    |
    | A Firebase PNV "test session" token copied from the Firebase console
    | (Phone Number Verification > test mode). When set, the verification flow
    | runs in SIM-less test mode against the Firebase backend with NO billing,
    | which is ideal for emulators and CI. getVerifiedPhoneNumber() will return
    | a fixed test phone number (a valid country code followed by zeros).
    |
    | Leave this null in production so real carrier verification is used.
    |
    */

    'test_token' => env('FIREBASE_PNV_TEST_TOKEN'),

];
