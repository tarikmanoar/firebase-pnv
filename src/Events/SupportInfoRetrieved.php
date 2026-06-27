<?php

namespace Manoar\FirebasePnv\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired in response to FirebasePNV::supportInfo(), reporting whether the
 * device/SIM(s) can use Firebase PNV.
 *
 * Payload keys must match the JSON emitted by the Kotlin bridge:
 *   - supported: bool   true if at least one SIM supports PNV
 *   - sims:      array  per-SIM detail: [{ index: int, supported: bool }, ...]
 *   - id:        string optional correlation id
 */
class SupportInfoRetrieved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public bool $supported,
        public array $sims = [],
        public ?string $id = null,
    ) {}
}
