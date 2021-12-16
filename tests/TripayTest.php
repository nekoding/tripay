<?php

namespace Nekoding\Tripay\Tests;

use Illuminate\Foundation\Testing\TestCase;
use Nekoding\Tripay\Tripay;
use Orchestra\Testbench\Concerns\CreatesApplication;

class TripayTest extends TestCase
{

    use CreatesApplication;

    public function test_init_configuration()
    {
        Tripay::loadConfig(true, 'api_key', 'private_key', 'merchant_code');

        $this->assertArrayHasKey('tripay_api_production', config('tripay'));
        $this->assertArrayHasKey('tripay_api_key', config('tripay'));
        $this->assertArrayHasKey('tripay_private_key', config('tripay'));

        $this->assertTrue(config('tripay.tripay_api_production'));
        $this->assertEquals('api_key', config('tripay.tripay_api_key'));
        $this->assertEquals('private_key', config('tripay.tripay_private_key'));
        $this->assertEquals('merchant_code', config('tripay.tripay_merchant_code'));
    }
}
