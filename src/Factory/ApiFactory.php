<?php


namespace App\Factory;


use App\Security\UserSession;
use GuzzleHttp\ClientInterface;
use MjOpenApi\Api\BallotApi;
use MjOpenApi\Api\PollApi;
use MjOpenApi\Api\ResultApi;
use MjOpenApi\Api\TokenApi;
use MjOpenApi\Api\UserApi;
use MjOpenApi\Configuration;


class ApiFactory
{
    /** @var UserSession */
    protected $session;

    /** @var Configuration */
    protected $config;

    /**
     * ApiFactory constructor.
     */
    public function __construct()
    {
        // Configure API key authorization: apiKey
        $this->config = Configuration::getDefaultConfiguration();
//        $this->config->setApiKey('Authorization', 'YOUR_API_KEY');
        // Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
        // $this->config->setApiKeyPrefix('Authorization', 'Bearer');
        $this->config->setHost("http://localhost:8001");
    }

    public function setToken(string $token)
    {
        $this->config->setAccessToken($token);
    }

    /**
     * @return UserSession
     */
    public function getSession(): UserSession
    {
        return $this->session;
    }

    /**
     * @required
     * @param UserSession $session
     */
    public function setSession(UserSession $session): void
    {
        $this->session = $session;
        $user = $session->getUser();
        if (null !== $user) {
            $this->setToken($user['token']);
        }
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

    public function getUserApi() : UserApi
    {
        $apiInstance = new UserApi($this->getClient(), $this->getConfig());
        return $apiInstance;
    }

    public function getTokenApi() : TokenApi
    {
        $apiInstance = new TokenApi($this->getClient(), $this->getConfig());
        return $apiInstance;
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

    public function getResultApi() : ResultApi
    {
        $apiInstance = new ResultApi($this->getClient(), $this->getConfig());
        return $apiInstance;
    }
}