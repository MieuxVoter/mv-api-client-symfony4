<?php


namespace App\Factory;


use GuzzleHttp\ClientInterface;
use MjOpenApi\Api\BallotApi;
use MjOpenApi\Api\PollApi;
use MjOpenApi\Configuration;


class ApiFactory
{
    protected $config;

    /**
     * ApiFactory constructor.
     */
    public function __construct()
    {
        // Configure API key authorization: apiKey
        $this->config = Configuration::getDefaultConfiguration();
        $this->config->setApiKey('Authorization', 'YOUR_API_KEY');
        // Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
        // $this->config->setApiKeyPrefix('Authorization', 'Bearer');
        $this->config->setHost("http://localhost:8001");
    }

    protected function getClient() : ClientInterface
    {
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            return new \GuzzleHttp\Client();
    }

    /**
     * @return Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    public function getPollApi() : PollApi
    {
        $apiInstance = new PollApi($this->getClient(), $this->getConfig());
        return $apiInstance;
    }

    public function getBallotApi() : BallotApi
    {
        $apiInstance = new BallotApi($this->getClient(), $this->getConfig());
        return $apiInstance;
    }
}