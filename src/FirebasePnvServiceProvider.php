<?php

namespace Manoar\FirebasePnv;

use Illuminate\Support\ServiceProvider;

class FirebasePnvServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/firebase-pnv.php', 'firebase-pnv');

        $this->app->singleton(PhoneNumberVerification::class, static fn () => new PhoneNumberVerification);
        $this->app->alias(PhoneNumberVerification::class, 'firebase-pnv');
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Publishable config.
        $this->publishes([
            __DIR__.'/../config/firebase-pnv.php' => config_path('firebase-pnv.php'),
        ], 'firebase-pnv-config');

        // Native bridge sources — published for NativePHP Mobile builds that do
        // not yet auto-consume the nativephp.json manifest (e.g. v2.x). See
        // README "Manual integration".
        $this->publishes([
            __DIR__.'/../resources/android' => base_path('nativephp/plugins/firebase-pnv/android'),
            __DIR__.'/../resources/ios' => base_path('nativephp/plugins/firebase-pnv/ios'),
        ], 'firebase-pnv-native');
    }
}
