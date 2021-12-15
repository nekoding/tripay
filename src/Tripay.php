<?php

namespace Nekoding\Tripay;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Nekoding\Tripay\Exceptions\InvalidTransactionException;
use Nekoding\Tripay\Networks\HttpClient;
use Nekoding\Tripay\Transactions\CloseTransaction;
use Nekoding\Tripay\Transactions\OpenTransaction;
use Nekoding\Tripay\Transactions\Transaction;
class Tripay
{

    /**
     *
     */
    const OPEN_TRANSACTION = 'open';
    /**
     *
     */
    const CLOSE_TRANSACTION = 'close';

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $code
     * @param string $payCode
     * @param string $amount
     * @param int $allowHtml
     * @return Collection
     * @link https://tripay.co.id/developer?tab=payment-instruction
     */
    public function getInstruksiPembayaran(
        string $code,
        string $payCode,
        string $amount,
        int $allowHtml = 1
    ): Collection 
    {
        $data = [
            'code'          => $code,
            'pay_code'      => $payCode,
            'amount'        => $amount,
            'allow_html'    => $allowHtml
        ];

        $result = $this->httpClient->sendRequest('GET', 'payment/instruction', $data);

        return collect(json_decode($result, true));
    }

    /**
     * @param string $code
     * @return Collection
     * @link https://tripay.co.id/developer?tab=merchant-payment-channel
     */
    public function getChannelPembayaran(string $code): Collection
    {
        $result = $this->httpClient->sendRequest('GET', 'merchant/payment-channel', [
            'code' => $code
        ]);

        return collect(json_decode($result, true));
    }

    /**
     * @param string $code
     * @param int $amount
     * @return Collection
     * @link https://tripay.co.id/developer?tab=merchant-fee-calculator
     */
    public function getBiayaTransaksi(string $code, int $amount): Collection
    {
        $data = [
            'code' => $code,
            'amount' => $amount
        ];

        $result = $this->httpClient->sendRequest('GET', 'merchant/fee-calculator', $data);

        return collect(json_decode($result, true));
    }

    /**
     * @param array $data
     * @return Collection
     * @link https://tripay.co.id/developer?tab=merchant-transactions
     */
    public function getDaftarTransaksi(array $data = []): Collection
    {
        $validator = Validator::make($data, [
            'page'          => 'bail|nullable|int',
            'per_page'      => 'bail|nullable|int',
            'sort'          => 'bail|nullable|in:asc,desc',
            'reference'     => 'bail|nullable|string',
            'merchant_ref'  => 'bail|nullable|string',
            'method'        => 'bail|nullable|string',
            'status'        => 'bail|nullable|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $result = $this->httpClient->sendRequest('GET', 'merchant/transactions', $validator->validate());

        return collect(json_decode($result, true));
    }

    /**
     * @param array $data
     * @param string $transactionType
     * @return Transaction
     * @throws Exceptions\InvalidCredentialException
     * @throws Exceptions\InvalidSignatureHashException
     * @throws InvalidTransactionException
     * @link https://tripay.co.id/developer?tab=transaction-create
     * @link https://tripay.co.id/developer?tab=open-payment-create
     */
    public function createTransaction(array $data, string $transactionType = 'close'): Transaction
    {

        if ($transactionType == self::OPEN_TRANSACTION) {
            $handler = new OpenTransaction($this->httpClient);
            return $handler->createTransaction($data);
        }

        if ($transactionType == self::CLOSE_TRANSACTION) {
            $handler = new CloseTransaction($this->httpClient);
            return $handler->createTransaction($data);
        }

        throw new InvalidTransactionException("metode yang digunakan tidak didukung.");
    }

    /**
     * @param string $id
     * @param string $transactionType
     * @return Transaction
     * @throws InvalidTransactionException
     * @link https://tripay.co.id/developer?tab=transaction-detail
     * @link https://tripay.co.id/developer?tab=open-payment-detail-
     */
    public function getDetailTransaction(string $id, string $transactionType = 'close'): Transaction
    {
        if ($transactionType == self::OPEN_TRANSACTION) {
            $handler = new OpenTransaction($this->httpClient);
            return $handler->getDetailTransaction($id);
        }

        if ($transactionType == self::CLOSE_TRANSACTION) {
            $handler = new CloseTransaction($this->httpClient);
            return $handler->getDetailTransaction($id);
        }

        throw new InvalidTransactionException("metode yang digunakan tidak didukung.");
    }
}
