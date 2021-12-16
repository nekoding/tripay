<?php

namespace Nekoding\Tripay;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nekoding\Tripay\Skeleton\SkeletonClass
 * @method static \Illuminate\Support\Collection getInstruksiPembayaran(string $code, string $payCode, string $amount, int $allowHtml = 1)
 * @method static \Illuminate\Support\Collection getChannelPembayaran(string $code)
 * @method static \Illuminate\Support\Collection getBiayaTransaksi(string $code, int $amount)
 * @method static \Illuminate\Support\Collection getDaftarTransaksi(array $data = [])
 * @method static \Nekoding\Tripay\Transactions\Transaction createTransaction(array $data, string $transactionType = 'close')
 * @method static \Nekoding\Tripay\Transactions\Transaction getDetailTransaction(string $id, string $transactionType = 'close')
 * @method static void loadConfig(bool $isProduction, string $apiKey, string $privateKey, string $merchantCode)
 */
class TripayFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'tripay';
    }
}
