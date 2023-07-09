<?php

namespace Nekoding\Tripay\Transactions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Nekoding\Tripay\Exceptions\InvalidCredentialException;
use Nekoding\Tripay\Exceptions\InvalidSignatureHashException;
use Nekoding\Tripay\Networks\HttpClient;
use Nekoding\Tripay\Signature;
use Nekoding\Tripay\Validator\CreateOpenTransactionFormValidation;

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
            throw new InvalidCredentialException("tidak dapat menggunakan api ini dalam mode sandbox.");
        }

        $validated = CreateOpenTransactionFormValidation::validate($data);

        if (!Signature::validate(
            $this->setSignatureHash($validated),
            $validated['signature']
        )) {
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
     * @param array $data
     * @return string
     * @throws \Nekoding\Tripay\Exceptions\InvalidCredentialException
     */
    public function setSignatureHash(array $data): string
    {

        if (isset($data['method']) && isset($data['merchant_ref'])) {
            return $data['method'] . $data['merchant_ref'];
        }

        throw new InvalidCredentialException("gagal melakukan hash. data method atau merchant_ref belum dikonfigurasi.");
    }

    /**
     * @param  string $uuid
     * @param  array $data
     * @return Transaction
     * @throws \Nekoding\Tripay\Exceptions\InvalidCredentialException
     */
    public function getDaftarPembayaran(string $uuid, array $data = []): Transaction
    {
        if (!config('tripay.tripay_api_production')) {
            throw new InvalidCredentialException("tidak dapat menggunakan api ini dalam mode sandbox.");
        }

        $validatedData = Validator::make($data, [
            'reference'     => 'bail|nullable|string',
            'merchant_ref'  => 'bail|nullable|string',
            'start_date'    => 'bail|nullable|string|date_format:Y-m-d H:i:s',
            'end_date'      => 'bail|nullable|string|date_format:Y-m-d H:i:s',
            'per_page'      => 'bail|nullable|int'
        ])->validate();

        $endpoint = "open-payment/" . $uuid . "/transactions";

        $this->response = $this->httpClient->sendRequest('GET', $endpoint, $validatedData);

        return $this;
    }
}
