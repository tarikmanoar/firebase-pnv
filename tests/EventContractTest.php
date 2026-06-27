<?php

use Manoar\FirebasePnv\Events\SupportInfoRetrieved;
use Manoar\FirebasePnv\Events\VerificationFailed;
use Manoar\FirebasePnv\Events\Verified;

/*
 * The native layer delivers async results by POSTing to /_native/api/events,
 * where NativePHP does `new $event(...$payload)`. Because PHP spreads a
 * string-keyed array as NAMED arguments, the event constructor parameter names
 * MUST match the JSON keys emitted by the Kotlin bridge (FirebasePnvFunctions.kt)
 * and the Swift stub. These tests lock that contract in.
 */

it('builds Verified from the native getVerifiedPhoneNumber payload', function () {
    $payload = ['phoneNumber' => '+15555550000', 'token' => 'signed.jwt.token', 'id' => 'checkout-42'];

    $event = new Verified(...$payload);

    expect($event->phoneNumber)->toBe('+15555550000')
        ->and($event->token)->toBe('signed.jwt.token')
        ->and($event->id)->toBe('checkout-42');
});

it('builds Verified when the optional id is omitted', function () {
    $event = new Verified(...['phoneNumber' => '+15555550000', 'token' => 'signed.jwt.token']);

    expect($event->id)->toBeNull();
});

it('builds SupportInfoRetrieved from the native getVerificationSupportInfo payload', function () {
    $payload = [
        'supported' => true,
        'sims' => [['index' => 0, 'supported' => true], ['index' => 1, 'supported' => false]],
        'id' => 'abc',
    ];

    $event = new SupportInfoRetrieved(...$payload);

    expect($event->supported)->toBeTrue()
        ->and($event->sims)->toHaveCount(2)
        ->and($event->sims[0]['supported'])->toBeTrue();
});

it('builds VerificationFailed for each failure code the bridge can emit', function (string $code) {
    $event = new VerificationFailed(...['code' => $code, 'message' => 'boom']);

    expect($event->code)->toBe($code)
        ->and($event->message)->toBe('boom')
        ->and($event->id)->toBeNull();
})->with([
    'SUPPORT_INFO_FAILED',
    'NOT_SUPPORTED',
    'VERIFICATION_FAILED',
    'UNSUPPORTED_PLATFORM',
]);
