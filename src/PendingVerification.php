<?php

namespace Manoar\FirebasePnv;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Manoar\FirebasePnv\Concerns\CallsNativeBridge;
use Manoar\FirebasePnv\Events\Verified;
use Manoar\FirebasePnv\Events\VerificationFailed;

/**
 * Fluent builder for a phone-number verification request.
 *
 * Triggers the native `FirebasePnv.GetVerifiedPhoneNumber` bridge function,
 * which runs the full Firebase PNV flow on Android: it uses the Android
 * Credential Manager to obtain the user's consent to share their carrier
 * phone number, calls the Firebase PNV backend, and returns a signed token.
 *
 * The verification is asynchronous. The outcome is delivered to your app as a
 * Laravel event:
 *   - success => Manoar\FirebasePnv\Events\Verified (or your custom event())
 *   - failure => Manoar\FirebasePnv\Events\VerificationFailed
 *
 * The request auto-starts on destruction if dispatch() was not called
 * explicitly, so `FirebasePNV::verify();` is enough to kick off the flow.
 */
class PendingVerification
{
    use CallsNativeBridge;

    protected ?string $id = null;

    protected string $successEvent;

    protected string $failureEvent;

    protected ?string $testToken = null;

    protected bool $started = false;

    public function __construct()
    {
        $this->successEvent = Verified::class;
        $this->failureEvent = VerificationFailed::class;
        $this->testToken = config('firebase-pnv.test_token');
    }

    /**
     * Set a correlation id so event listeners can match this request.
     */
    public function id(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get this request's correlation id (lazily generating a UUID).
     */
    public function getId(): string
    {
        return $this->id ??= (string) Str::uuid();
    }

    /**
     * Override the event dispatched on success. Must accept the named
     * arguments: phoneNumber, token, id.
     */
    public function event(string $eventClass): self
    {
        $this->assertEventExists($eventClass);
        $this->successEvent = $eventClass;

        return $this;
    }

    /**
     * Override the event dispatched on failure/cancellation. Must accept the
     * named arguments: code, message, id.
     */
    public function failureEvent(string $eventClass): self
    {
        $this->assertEventExists($eventClass);
        $this->failureEvent = $eventClass;

        return $this;
    }

    /**
     * Run in Firebase PNV "test session" mode using a token copied from the
     * Firebase console. Lets you exercise the flow on a SIM-less device/emulator
     * with no billing. Pass null to fall back to the configured default token.
     */
    public function test(?string $token = null): self
    {
        $this->testToken = $token ?? config('firebase-pnv.test_token');

        return $this;
    }

    /**
     * Flash this request's id into the session so event listeners on the next
     * request can correlate it via PendingVerification::lastId().
     */
    public function remember(): self
    {
        session()->flash('_firebase_pnv_id', $this->getId());

        return $this;
    }

    /**
     * The id of the most recently remember()'d verification request.
     */
    public static function lastId(): ?string
    {
        return session('_firebase_pnv_id');
    }

    /**
     * Hand the verification request to the native layer. Idempotent.
     */
    public function dispatch(): bool
    {
        if ($this->started) {
            return false;
        }

        $this->started = true;

        $payload = array_filter([
            'id' => $this->getId(),
            'event' => $this->successEvent,
            'failEvent' => $this->failureEvent,
            'testToken' => $this->testToken,
        ], static fn ($value) => $value !== null);

        return $this->callNative('FirebasePnv.GetVerifiedPhoneNumber', $payload);
    }

    protected function assertEventExists(string $eventClass): void
    {
        if (! class_exists($eventClass)) {
            throw new InvalidArgumentException("Event class {$eventClass} does not exist");
        }
    }

    /**
     * Auto-start the flow if dispatch() was not called explicitly, preserving
     * the simple `FirebasePNV::verify();` entry point.
     */
    public function __destruct()
    {
        if (! $this->started) {
            $this->dispatch();
        }
    }
}
