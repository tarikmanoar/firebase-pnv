<?php

namespace Manoar\FirebasePnv;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Manoar\FirebasePnv\Concerns\CallsNativeBridge;
use Manoar\FirebasePnv\Events\SupportInfoRetrieved;
use Manoar\FirebasePnv\Events\VerificationFailed;

/**
 * Fluent builder for a device/SIM capability check.
 *
 * Triggers the native `FirebasePnv.GetVerificationSupportInfo` bridge function
 * (Firebase PNV `getVerificationSupportInfo()`). The result is asynchronous and
 * delivered as a Laravel event:
 *   - success => Manoar\FirebasePnv\Events\SupportInfoRetrieved
 *   - failure => Manoar\FirebasePnv\Events\VerificationFailed
 *
 * Auto-starts on destruction if check() was not called explicitly.
 */
class PendingSupportInfo
{
    use CallsNativeBridge;

    protected ?string $id = null;

    protected string $successEvent;

    protected string $failureEvent;

    protected ?string $testToken = null;

    protected bool $started = false;

    public function __construct()
    {
        $this->successEvent = SupportInfoRetrieved::class;
        $this->failureEvent = VerificationFailed::class;
        $this->testToken = config('firebase-pnv.test_token');
    }

    public function id(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): string
    {
        return $this->id ??= (string) Str::uuid();
    }

    /**
     * Override the event dispatched on success. Must accept the named
     * arguments: supported, sims, id.
     */
    public function event(string $eventClass): self
    {
        $this->assertEventExists($eventClass);
        $this->successEvent = $eventClass;

        return $this;
    }

    public function failureEvent(string $eventClass): self
    {
        $this->assertEventExists($eventClass);
        $this->failureEvent = $eventClass;

        return $this;
    }

    public function test(?string $token = null): self
    {
        $this->testToken = $token ?? config('firebase-pnv.test_token');

        return $this;
    }

    /**
     * Hand the support check to the native layer. Idempotent.
     */
    public function check(): bool
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

        return $this->callNative('FirebasePnv.GetVerificationSupportInfo', $payload);
    }

    protected function assertEventExists(string $eventClass): void
    {
        if (! class_exists($eventClass)) {
            throw new InvalidArgumentException("Event class {$eventClass} does not exist");
        }
    }

    public function __destruct()
    {
        if (! $this->started) {
            $this->check();
        }
    }
}
