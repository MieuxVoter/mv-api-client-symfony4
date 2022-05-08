<?php


namespace App\Controller\Has;


use App\Adapter\ApiExceptionAdapter;
use App\Entity\User;
use App\Factory\ApiFactory;
use Exception;
use MvApi\ApiException;
use MvApi\Model\Credentials;
use MvApi\Model\UserCreate;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;


trait ApiAccess
{
    use UserSession;
    use FlashBag;
    use Translator;

    /** @var ApiFactory $api_factory */
    protected $api_factory;

    /** @var ApiExceptionAdapter $api_exception_adapter */
    protected $api_exception_adapter;

    ///
    ///

    /**
     * @return ApiFactory
     */
    public function getApiFactory() : ApiFactory
    {
        return $this->api_factory;
    }

    /**
     * @required
     * @param ApiFactory $api_factory
     */
    public function setApiFactory(ApiFactory $api_factory): void
    {
        $this->api_factory = $api_factory;
    }

    /**
     * @return ApiExceptionAdapter
     */
    public function getApiExceptionAdapter(): ApiExceptionAdapter
    {
        return $this->api_exception_adapter;
    }

    /**
     * @required
     * @param ApiExceptionAdapter $api_exception_adapter
     */
    public function setApiExceptionAdapter(ApiExceptionAdapter $api_exception_adapter): void
    {
        $this->api_exception_adapter = $api_exception_adapter;
    }

    public function getApiExceptionData(ApiException $exception)
    {
        return $this->getApiExceptionAdapter()->toData($exception);
    }

    /**
     * Sugar to handle api exceptions in controllers.
     * NO.
     * We need a way to get multiple Services, this can't be in this Trait.
     * Perhaps it's okay to load the services we need, on second thought.
     *
     *
     * @param ApiException $exception
     * @return Response
     */
    public function renderApiException(ApiException $exception, Request $request) : Response
    {
        $data = $this->getApiExceptionData($exception);

        if (isset($data['code']) && (Response::HTTP_UNAUTHORIZED == $data['code'])) {
            // We should discriminate JWT token expiration from other 401 (upstream)
            $this->userSession->logout();
            session_destroy(); // bit harsh, but forces logout
            $this->getFlashBag()->add('warning', $this->trans("flash.error.requires_authentication"));
            // todo: redirect to /gate.html
            return new RedirectResponse("/login.html?redirect=".urlencode($request->getRequestUri()));
        }

        $apiResponse = $this->getApiExceptionAdapter()->toHtml($exception);

        return $this->render('error/api_exception.html.twig', [
            'apiResponse' => $apiResponse,
            'isDev' => $request->server->get('APP_ENV') == 'dev',
        ]);
    }

    public function quickRegister(Request $request, GuardAuthenticatorHandler $guard)
    {
        $userApi = $this->getApiFactory()->getUserApi();

        $passwordPlain = uniqid();

        $userCreate = new UserCreate();
        $userCreate->setPassword($passwordPlain);

        $userRead = null;
        try {
            $userRead = $userApi->postUserCollection($userCreate);
        } catch (ApiException $e) {
            return new Response("Quick registration failed: " . $e->getMessage());
        }

        // The registration seemed to work.  Let's login, if we can.

        $tokenApi = $this->getApiFactory()->getTokenApi();

        $credentials = new Credentials();
        $credentials->setUsernameOrEmail($userRead->getUsername());
        $credentials->setPassword($passwordPlain);


        $apiToken = null;
        try {
            $apiToken = $tokenApi->postCredentialsItem($credentials);
        } catch (ApiException $e) {
            // Registration was a success, but login was not.
            return $this->renderApiException($e, $request);
        }

        $user = new User();
        $user->setUsername($userRead->getUsername());
        $user->setApiToken($apiToken->getToken());

        $this->userSession->login(
            $userRead->getUuid(),
            $userRead->getUsername(),
            $apiToken->getToken()
        );
        $this->getApiFactory()->setApiToken($apiToken->getToken());



        // Authenticate with Symfony
        $sfToken = new UsernamePasswordToken($user, null, 'mvapi_users', $user->getRoles());
        $guard->authenticateWithToken($sfToken, $request, 'mvapi_users');

        // Wipe the memory…
        $passwordPlain = uniqid();
        $apiToken = md5($passwordPlain);
        unset($passwordPlain);
        unset($apiToken);
        unset($sfToken);
        // /!\ … nope (but better than nothing).  Use a proper mem0, there's one in php exts.

        return true;
    }

    /**
     * Allows hooking in JWT refresh if needed.
     *
     * @param $out
     * @param Callable $onTry
     * @param Callable $onFailure
     * @throws Exception
     */
    public function tryApi(&$out, $onTry, $onFailure)
    {
        try {
            $out = $onTry();
        } catch (ApiException $e) {
            $onFailure($e);
        } catch (Exception $e) {
            throw $e;
        }
    }
}