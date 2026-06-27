<?php

namespace Manoar\FirebasePnv\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Manoar\FirebasePnv\PendingVerification verify()
 * @method static \Manoar\FirebasePnv\PendingSupportInfo supportInfo()
 *
 * @see \Manoar\FirebasePnv\PhoneNumberVerification
 */
class FirebasePNV extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Manoar\FirebasePnv\PhoneNumberVerification::class;
    }
}
