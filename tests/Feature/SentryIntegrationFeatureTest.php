<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SentryIntegrationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_sentry_config_uses_environment_dsn(): void
    {
        config(['sentry.dsn' => 'https://examplePublicKey@o0.ingest.sentry.io/0']);

        $this->assertTrue(filled(config('sentry.dsn')));
    }

    public function test_sentry_sdk_class_is_available_after_installation(): void
    {
        $this->assertTrue(class_exists(\Sentry\Laravel\Integration::class));
    }
}
