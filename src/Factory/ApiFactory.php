<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use App\Has\ApiLogger;
use App\Security\UserSession;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use MvApi\Api\BallotApi;
use MvApi\Api\InvitationApi;
use MvApi\Api\PollApi;
use MvApi\Api\ResultApi;
use MvApi\Api\LoginApi;
use MvApi\Api\UserApi;
use MvApi\Configuration;
use Symfony\Component\Security\Core\Security;


/**
 * Helper Service to configure communication with MV OpenAPI OASv3.
 *
 * Class ApiFactory
 * @package App\Factory
 */
final class ApiFactory
{
    use ApiLogger;

    /** @var UserSession */
    protected $session;

    /** @var Configuration */
    protected $config;

    /** @var Security $security */
    protected $security;

    /**
     * ApiFactory constructor.
     */
    public function __construct()
    {
        $this->config = Configuration::getDefaultConfiguration();
        $host = null;
        // Careful, getenv might return false here since the value is defined by .env
        //$host = getenv("OAS_URL");
        // Instead, get the value from $_ENV
        if (isset($_ENV['OAS_URL'])) {
            $host = $_ENV['OAS_URL'];
        }
        if (!$host) {
            trigger_error("OAS_URL environment variable is not set.");
        }
        $this->config->setHost($host);
        //$this->config->setHost("http://localhost:8000");
        //$this->config->setHost("https://oas.mieuxvoter.fr");
    }

    /**
     * @required → Called by the Dependency Injection Container
     * @param Security $security
     *
     * Ongoing Experiment to see when this annotation will actually work in here in IDEs
     * - KO: PhpStorm 2019.2
     * - KO: 5+ Notepads
     * - KO: …
     * - OK: ?
     * @noinspection PhpUnused
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    /** @noinspection PhpUnused */
    /**
     * @required → Called by the Dependency Injection Container
     * @param UserSession $session
     */
    public function setSession(UserSession $session): void
    {
        $this->session = $session;
    }

    /**
     * @return UserSession The service injected by the DIC
     */
    protected function getSession(): UserSession
    {
        return $this->session;
    }

    /**
     * Since we might have multiple API tokens, one for user auth and one for client auth,
     * perhaps this needs to be renamed to indicate it means the user auth API token.
     *
     * @param string $apiToken
     */
    public function setApiToken(string $apiToken)
    {
        $this->config->setApiKey('Authorization', $apiToken);
        $this->config->setApiKeyPrefix('Authorization', 'Bearer');
//        $this->config->setApiAccessToken($token); // later, for client auth API token (optional, for IP forwarding privileges)
    }

    protected function getClient(): ClientInterface
    {
        $user = $this->security->getUser();
        if ($user instanceof User) {
            // I'm not sure why the security user does not have our token.
//            $token = $user->getApiToken();
            // We need to figure out how to configure sf to save custom user fields in session,
            // to remove the need for the UserSession service.
            // I especially dislike its associative array API.
            $token = $this->getSession()->getUser()['token'];
            if ( ! empty($token)) {
                $this->setApiToken($token);
            }
        }

        $handler = HandlerStack::create();
        $handler->push($this->getApiLogger()->cookClientMiddleware());
        $config = [
            'handler' => $handler,
        ];
        return new Client($config);
    }

    /**
     * @return Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    public function getUserApi(): UserApi
    {
        $apiInstance = new UserApi($this->getClient(), $this->getConfig());
        return $apiInstance;
    }

    public function getLoginApi(): LoginApi
    {
        $apiInstance = new LoginApi($this->getClient(), $this->getConfig());
        return $apiInstance;
    }

    public function getPollApi(): PollApi
    {
        $apiInstance = new PollApi($this->getClient(), $this->getConfig());
        return $apiInstance;
    }

    public function getBallotApi(): BallotApi
    {
        $apiInstance = new BallotApi($this->getClient(), $this->getConfig());
        return $apiInstance;
    }

    public function getResultApi(): ResultApi
    {
        $apiInstance = new ResultApi($this->getClient(), $this->getConfig());
        return $apiInstance;
    }

    public function getInvitationApi(): InvitationApi
    {
        $apiInstance = new InvitationApi($this->getClient(), $this->getConfig());
        return $apiInstance;
    }
}
