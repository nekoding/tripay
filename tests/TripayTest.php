<?php

namespace Nekoding\Tripay\Tests;

use Illuminate\Foundation\Testing\TestCase;
use Orchestra\Testbench\Concerns\CreatesApplication;

class TripayTest extends TestCase
{

    use CreatesApplication;

    protected function loadConfig(): void
    {
        config()->set('tripay', array_merge(
            require __DIR__ . '/../config/config.php',
            config()->get('tripay', [])
        ));
    }

    public function test_init_configuration()
    {
        $this->loadConfig();

        $this->assertArrayHasKey('tripay_api_production', config('tripay'));
        $this->assertArrayHasKey('tripay_api_key', config('tripay'));
        $this->assertArrayHasKey('tripay_private_key', config('tripay'));
    }
}
