<?php

namespace Nekoding\Tripay\Tests;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Mockery\MockInterface;
use Nekoding\Tripay\Exceptions\TripayValidationException;
use Nekoding\Tripay\Networks\HttpClient;
use Nekoding\Tripay\Signature;
use Nekoding\Tripay\Transactions\CloseTransaction;
use Nekoding\Tripay\Tripay;
use Nekoding\Tripay\TripayFacade;
use Nekoding\Tripay\TripayServiceProvider;
use Orchestra\Testbench\Concerns\CreatesApplication;

class TripayTest extends TestCase
{

    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // load tripay service provider
        $this->createApplication()
            ->resolveProvider(TripayServiceProvider::class)
            ->register();
    }

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

    public function test_create_open_transaction()
    {
        $data = [
            'method'         => 'BRIVA',
            'merchant_ref'   => 'INV12345',
            'customer_name'  => 'Nama Pelanggan',
            'signature'      => Signature::generate('BRIVAINV12345')
        ];

        // fake api production
        config(['tripay.tripay_api_production' => true]);

        /**
         * @var \Nekoding\Tripay\Networks\HttpClient
         */
        $fakeHttpClient = $this->mock(HttpClient::class, function (MockInterface $mock) {
            return $mock->shouldReceive('sendRequest')
                ->once()
                ->andReturn(file_get_contents(__DIR__ . '/mock/open_transaction/success.json'));
        });

        $tripay = new Tripay($fakeHttpClient);
        $result = $tripay->createTransaction($data, Tripay::OPEN_TRANSACTION)->getResponse();

        $this->assertTrue($result['success']);
    }

    public function test_create_close_transaction()
    {
        $data = [
            'method'         => 'BRIVA',
            'merchant_ref'   => 'KODE INVOICE',
            'amount'         => 50000,
            'customer_name'  => 'Nama Pelanggan',
            'customer_email' => 'emailpelanggan@domain.com',
            'customer_phone' => '081234567890',
            'order_items'    => [
                [
                    'sku'         => 'FB-06',
                    'name'        => 'Nama Produk 1',
                    'price'       => 50000,
                    'quantity'    => 1,
                    'product_url' => 'https://tokokamu.com/product/nama-produk-1',
                    'image_url'   => 'https://tokokamu.com/product/nama-produk-1.jpg',
                ]
            ],
            'return_url'   => 'https://domainanda.com/redirect',
            'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
            'signature'    => Signature::generate('KODE INVOICE' . 50000)
        ];

        /**
         * @var \Nekoding\Tripay\Networks\HttpClient
         */
        $fakeHttpClient = $this->mock(HttpClient::class, function (MockInterface $mock) {
            return $mock->shouldReceive('sendRequest')
                ->once()
                ->andReturn(file_get_contents(__DIR__ . '/mock/close_transaction/success.json'));
        });

        $tripay = new Tripay($fakeHttpClient);
        $result = $tripay->createTransaction($data)->getResponse();

        $this->assertTrue($result['success']);
    }

    public function test_get_detail_closed_transaction()
    {
        /**
         * @var \Nekoding\Tripay\Networks\HttpClient
         */
        $fakeHttpClient = $this->mock(HttpClient::class, function (MockInterface $mock) {
            return $mock->shouldReceive('sendRequest')
                ->once()
                ->andReturn(file_get_contents(__DIR__ . '/mock/close_transaction/detail_success.json'));
        });

        $tripay = new Tripay($fakeHttpClient);
        $result = $tripay->getDetailTransaction('T0001000000000000006')->getResponse();

        $this->assertTrue($result['success']);
        $this->assertEquals('T0001000000000000006', $result['data']['reference']);
    }

    public function test_get_detail_open_transaction()
    {
        /**
         * @var \Nekoding\Tripay\Networks\HttpClient
         */
        $fakeHttpClient = $this->mock(HttpClient::class, function (MockInterface $mock) {
            return $mock->shouldReceive('sendRequest')
                ->once()
                ->andReturn(file_get_contents(__DIR__ . '/mock/open_transaction/detail_success.json'));
        });

        $tripay = new Tripay($fakeHttpClient);
        $result = $tripay->getDetailTransaction('T0001OP9376HnpS', Tripay::OPEN_TRANSACTION)->getResponse();

        $this->assertTrue($result['success']);
        $this->assertEquals('T0001OP9376HnpS', $result['data']['uuid']);
    }

    public function test_get_instruksi_pembayaran()
    {
        /**
         * @var \Nekoding\Tripay\Networks\HttpClient
         */
        $fakeHttpClient = $this->mock(HttpClient::class, function (MockInterface $mock) {
            return $mock->shouldReceive('sendRequest')
                ->once()
                ->andReturn(file_get_contents(__DIR__ . '/mock/instruksi_pembayaran/success.json'));
        });

        $tripay = new Tripay($fakeHttpClient);

        $result = $tripay->getInstruksiPembayaran('BRIVA');
        // $result = $tripay->getInstruksiPembayaran('BRIVA', '264006510417648');
        // $result = $tripay->getInstruksiPembayaran('BRIVA', '264006510417648', '50000');

        $this->assertTrue($result['success']);
    }

    public function test_get_channel_pembayaran()
    {
        /**
         * @var \Nekoding\Tripay\Networks\HttpClient
         */
        $fakeHttpClient = $this->mock(HttpClient::class, function (MockInterface $mock) {
            return $mock->shouldReceive('sendRequest')
                ->once()
                ->andReturn(file_get_contents(__DIR__ . '/mock/channel_pembayaran/success.json'));
        });

        $tripay = new Tripay($fakeHttpClient);

        $result = $tripay->getChannelPembayaran();
        // $result = $tripay->getChannelPembayaran('PERMATAVA');
        $this->assertTrue($result['success']);
        $this->assertCount(14, $result['data']);
    }

    public function test_get_biaya_transaksi()
    {
        /**
         * @var \Nekoding\Tripay\Networks\HttpClient
         */
        $fakeHttpClient = $this->mock(HttpClient::class, function (MockInterface $mock) {
            return $mock->shouldReceive('sendRequest')
                ->once()
                ->andReturn(file_get_contents(__DIR__ . '/mock/biaya_transaksi/success.json'));
        });

        $tripay = new Tripay($fakeHttpClient);

        $result = $tripay->getBiayaTransaksi(100000, 'BRIVA');
        // $result = $tripay->getBiayaTransaksi(100000);
        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['data']);
    }

    public function test_get_daftar_transaksi()
    {
        /**
         * @var \Nekoding\Tripay\Networks\HttpClient
         */
        $fakeHttpClient = $this->mock(HttpClient::class, function (MockInterface $mock) {
            return $mock->shouldReceive('sendRequest')
                ->once()
                ->andReturn(file_get_contents(__DIR__ . '/mock/daftar_transaksi/success.json'));
        });

        $payload = [
            'page' => 1,
            'per_page' => 3,
            'sort' => 'desc',
        ];

        $tripay = new Tripay($fakeHttpClient);

        $result = $tripay->getDaftarTransaksi($payload);

        $this->assertTrue($result['success']);
    }

    /**
     * Test generate signature hash tripay
     * 
     * Referensi: https://tripay.co.id/developer?tab=transaction-signature-create
     *
     * @return void
     */
    public function test_generate_and_validate_signature_hash()
    {
        $privateKey   = config('tripay.tripay_private_key');
        $merchantCode = config('tripay.tripay_merchant_code');
        $merchantRef  = 'INV55567';
        $amount       = 1500000;

        $signature = hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey);
        $generatedSignature = Signature::generate($merchantRef . $amount);

        $this->assertEquals($signature, $generatedSignature);
        $this->assertTrue(Signature::validate($merchantRef . $amount, $signature));
    }

    public function test_validation_parameter_exception()
    {
        $this->expectException(TripayValidationException::class);

        TripayFacade::createTransaction([]);
    }
}
