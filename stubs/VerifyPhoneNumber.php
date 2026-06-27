<?php

// EXAMPLE — a Livewire component that triggers verification and reacts to the
// result in real time using NativePHP's #[OnNative] attribute (which listens for
// the front-end "native:<EventClass>" Livewire event the native layer fires).

namespace App\Livewire;

use Livewire\Component;
use Manoar\FirebasePnv\Events\Verified;
use Manoar\FirebasePnv\Events\VerificationFailed;
use Manoar\FirebasePnv\Facades\FirebasePNV;
use Native\Mobile\Attributes\OnNative;

class VerifyPhoneNumber extends Component
{
    public ?string $phoneNumber = null;

    public ?string $error = null;

    public bool $verifying = false;

    /**
     * Kick off the Firebase PNV flow. The native Credential Manager UI appears,
     * and the result comes back to onVerified()/onFailed() below.
     */
    public function startVerification(): void
    {
        $this->reset('phoneNumber', 'error');
        $this->verifying = true;

        FirebasePNV::verify()->id('livewire-'.$this->getId());
    }

    #[OnNative(Verified::class)]
    public function onVerified(string $phoneNumber, string $token, ?string $id = null): void
    {
        $this->verifying = false;
        $this->phoneNumber = $phoneNumber;

        // TODO: POST $token to your backend and verify it before trusting it.
    }

    #[OnNative(VerificationFailed::class)]
    public function onFailed(string $code, string $message, ?string $id = null): void
    {
        $this->verifying = false;
        $this->error = "{$code}: {$message}";
    }

    public function render()
    {
        return view('livewire.verify-phone-number');
    }
}
