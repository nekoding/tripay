<?php

namespace Nekoding\Tripay\Transactions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Nekoding\Tripay\Exceptions\InvalidCredentialException;
use Nekoding\Tripay\Exceptions\InvalidSignatureHashException;
use Nekoding\Tripay\Networks\HttpClient;
use Nekoding\Tripay\Signature;
use Nekoding\Tripay\Validator\CreateOpenTransactionFormValidation;
use Psr\Http\Message\ResponseInterface;

class OpenTransaction implements Transaction
{

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected string $response;

    /**
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param array $data
     * @return Transaction
     * @throws InvalidCredentialException
     * @throws InvalidSignatureHashException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createTransaction(array $data): Transaction
    {
        if (!config('tripay.tripay_api_production')) {
            throw new InvalidCredentialException("tidak dapat menggunakan tipe ini jika konfigurasi api sandbox.");
        }

        $validated = CreateOpenTransactionFormValidation::validate($data);

        if (!Signature::validate(
            $this->setSignatureHash($validated['method'] . $validated['merchant_ref']), 
            $validated['signature'])) 
        {
            throw new InvalidSignatureHashException("siganture hash salah. silahkan coba lagi.");
        }

        $this->response = $this->httpClient->sendRequest('POST', 'open-payment/create', $validated);

        return $this;
    }

    /**
     * @param string $uuid
     * @return Transaction
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getDetailTransaction(string $uuid): Transaction
    {
        $validated = Validator::make(['uuid' => $uuid], [
            'uuid' => 'required|string'
        ])->validate();

        $endpoint = "open-payment/" . $validated['uuid'] . "/detail";

        $this->response = $this->httpClient->sendRequest('GET', $endpoint, []);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getResponse(): Collection
    {
        return collect(json_decode($this->response, true));
    }

    /**
     * @param string $data
     * @return string
     */
    public function setSignatureHash(string $data): string
    {
        return config('tripay.tripay_merchant_code') . $data;
    }
}
