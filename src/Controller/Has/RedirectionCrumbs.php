<?php

declare(strict_types=1);

namespace App\Controller\Has;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Service\RedirectionCrumbs as Crumbs;
use Symfony\Component\Routing\RouterInterface;

/**
 * Usually this would be a middleware.
 * We have both the middleware (for grabbing the redirect_to query parameter),
 * and the trait for convenience in controllers.
 * There's a Service to glue the middleware and the database (user session),
 * and this trait wraps that service.
 *
 * Trait RedirectionCrumbs
 * @package App\Controller\Has
 */
trait RedirectionCrumbs
{

    /** @var Crumbs $actor */
    protected $crumbs;

    /** @var RouterInterface $router */
    protected $router;


    /**
     * @return Crumbs
     */
    public function getCrumbs(): Crumbs
    {
        return $this->crumbs;
    }

    /**
     * @required
     * @param Crumbs $crumbs
     */
    public function setCrumbs(Crumbs $crumbs): void
    {
        $this->crumbs = $crumbs;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @required
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    /**
     * Creates a RedirectResponse to the latest crumb, and eats that crumb.
     *
     * @param string $defaultRoute
     * @return Response
     */
    public function redirectToCrumb($defaultRoute = null): Response
    {
        $crumb = $this->getCrumbs()->consumeCrumb();
        if (empty($crumb)) {
            if ( ! empty($defaultRoute)) {
                $crumb = $this->getRouter()->generate($defaultRoute);
            } else {
                $crumb = $this->getDefaultCrumb();
            }
        }

        return RedirectResponse::create($crumb);
    }

    /**
     * Something to override in order to configure the default route.
     *
     * @return string
     */
    public function getDefaultCrumb(): string
    {
        return $this->getRouter()->generate('home');
    }
}