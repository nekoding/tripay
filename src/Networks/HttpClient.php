<?php

namespace Nekoding\Tripay\Networks;

use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use League\Config\Exception\InvalidConfigurationException;

class HttpClient
{

    const HTTP_GET = 'GET';

    const HTTP_POST = 'POST';

    /**
     * @var string
     */
    protected $sandboxURL = 'https://tripay.co.id/api-sandbox/';

    /**
     * @var string
     */
    protected $productionURL = 'https://tripay.co.id/api/';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param string|null $apiKey
     */
    public function __construct(string $apiKey = null)
    {
        $apiKey = $apiKey ?? config('tripay.tripay_api_key');

        if (is_null($apiKey)) {
            throw new InvalidConfigurationException("API_KEY belum dikonfigurasi");
        }

        $this->client = new Client([
            'base_uri'  => config('tripay.tripay_api_production') ?
                $this->productionURL :
                $this->sandboxURL,
            'headers'   => [
                'Authorization' => 'Bearer ' . $apiKey
            ]
        ]);
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return string
     * @throws Exception
     */
    public function sendRequest(string $method, string $endpoint, array $data)
    {
        if ($method == self::HTTP_GET) {
            return $this->sendGetRequest($endpoint, $data);
        }

        if ($method == self::HTTP_POST) {
            return $this->sendPostRequest($endpoint, $data);
        }

        throw new InvalidArgumentException(sprintf("http method %s tidak didukung.", $method));
    }

    /**
     * @param string $endpoint
     * @param array $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendGetRequest(string $endpoint, array $data)
    {
        try {
            $result = $this->client->get($endpoint, [
                'query' => $data
            ]);

            return $result->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\ClientException $th) {
            throw new Exception($th->getResponse()->getBody()->getContents());
        }
    }

    /**
     * @param string $endpoint
     * @param array $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendPostRequest(string $endpoint, array $data)
    {
        try {
            $result = $this->client->post($endpoint, [
                'form_params' => $data
            ]);

            return $result->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\ClientException $th) {
            throw new Exception($th->getResponse()->getBody()->getContents());
        }
    }
}
