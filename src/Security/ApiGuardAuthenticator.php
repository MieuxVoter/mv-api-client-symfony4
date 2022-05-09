<?php

declare(strict_types=1);

namespace App\Security;

use App\Controller\Has;
use App\Entity\User;
use MvApi\ApiException;
use MvApi\Model\Credentials;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;


/** @noinspection PhpUnused */

/**
 * Declared in config/packages/security.yaml
 *
 * Class ApiGuardAuthenticator
 * @package App\Security
 */
class ApiGuardAuthenticator extends AbstractGuardAuthenticator
{

    use TargetPathTrait;

    // Using the traits of Controllers, here ; perhaps bump up those we use?
    use Has\ApiAccess;
    use Has\FlashBag;
    use Has\Translator;
    use Has\UserSession;

    /**
     * Returns a response that directs the user to authenticate.
     *
     * This is called when an anonymous request accesses a resource that
     * requires authentication. The job of this method is to return some
     * response that "helps" the user start into the authentication process.
     *
     * Examples:
     *
     * - For a form login, you might redirect to the login page
     *
     *     return new RedirectResponse('/login');
     *
     * - For an API token authentication system, you return a 401 response
     *
     *     return new Response('Auth header required', 401);
     *
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse('/login.html');
    }

    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return (
            ($request->isMethod('POST'))
            &&
            (
                ('login_html' === $request->attributes->get('_route'))
                ||
                ('login' === $request->attributes->get('_route'))
            )
        );
    }

    /**
     * Get the authentication credentials from the request and return them
     * as any type (e.g. an associate array).
     *
     * Whatever value you return here will be passed to getUser() and checkCredentials()
     *
     * For example, for a form login, you might:
     *
     *      return [
     *          'username' => $request->request->get('_username'),
     *          'password' => $request->request->get('_password'),
     *      ];
     *
     * Or for an API token that's on a header, you might use:
     *
     *      return ['api_key' => $request->headers->get('X-API-TOKEN')];
     *
     * @param Request $request
     * @return mixed Any non-null value
     */
    public function getCredentials(Request $request)
    {
        return [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
        ];
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param mixed $credentials
     *
     * @param UserProviderInterface $userProvider
     * @return UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        $apiCredentials = new Credentials();
        $apiCredentials->setUsernameOrEmail($credentials['username']);
        $apiCredentials->setPassword($credentials['password']);

        $token = null;
        try {
            $token = $this
                ->getApiFactory()
                ->getLoginApi()
                ->postCredentialsItem($apiCredentials);
//                ->postCredentialsItemAsync($apiCredentials)
//                ->wait();
        } catch (ApiException $e) {
            // Here we should only return null when it's a 401,
            // and record the exception when it's not.
            //throw $e;
            return null;
        }

        $user = new User();
        $user->setUsername($credentials['username']);
        $user->setApiToken($token->getToken());

        $this->getApiFactory()->setApiToken($token->getToken());

        $myself = null;
        try {
            $myself = $this
                ->getApiFactory()
                ->getLoginApi()
                ->getMyself();
//                ->getMyselfAsync()
//                ->wait();
        } catch (ApiException $e) {
            // FIXME
            // We can probably return null, but perhaps a flash message is in order.
            // And a log of the exception, as well.
            throw $e;
            return null;
        }

//        dump($myself);
        if (null === $myself) {
            return null;
        }

//        dump($user);
//        dump($token);

        $this->getUserSession()->login(
            $myself->getUuid(),
            $myself->getUsername(),
            $token->getToken()
        );

        return $user;
    }

    /**
     * Returns true if the credentials are valid.
     *
     * If false is returned, authentication will fail. You may also throw
     * an AuthenticationException if you wish to cause authentication to fail.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * @param mixed $credentials
     *
     * @param UserInterface $user
     * @return bool
     *
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        // This method in only called if getUser() returns a User,
        // and in that case we already know the credentials are correct
        // because otherwise the User would be null and this would not be called.
        return true;
    }

    /**
     * Called when authentication executed, but failed (e.g. wrong username password).
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the login page or a 401 response.
     *
     * If you return null, the request will continue, but the user will
     * not be authenticated. This is probably not what you want to do.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->getFlashBag()->add('error', $this->trans("form.login.error.authentication_failure"));
        return new RedirectResponse('/login.html');
    }


    /**
     * Called when authentication executed and was successful!
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the last page they visited.
     *
     * If you return null, the current request will continue, and the user
     * will be authenticated. This makes sense, for example, with an API.
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey The provider (i.e. firewall) key
     *
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }
        return null;
    }

    /**
     * Does this method support remember me cookies?
     *
     * Remember me cookie will be set if *all* of the following are met:
     *  A) This method returns true
     *  B) The remember_me key under your firewall is configured
     *  C) The "remember me" functionality is activated. This is usually
     *      done by having a _remember_me checkbox in your form, but
     *      can be configured by the "always_remember_me" and "remember_me_parameter"
     *      parameters under the "remember_me" firewall key
     *  D) The onAuthenticationSuccess method returns a Response object
     *
     * @return bool
     */
    public function supportsRememberMe(): bool
    {
        return false; // our Provider needs work before we can support true
    }
}