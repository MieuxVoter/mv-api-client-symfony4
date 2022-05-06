<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\RedirectionCrumbs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
//use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Middleware portion of the Crumbs service.
 * Ran on each request, right before the controller.
 *
 * Class RedirectionCrumbsSubscriber
 * @package App\EventSubscriber
 */
class RedirectionCrumbsSubscriber implements EventSubscriberInterface
{

//    /** @var Request $request */
//    protected $request;

    /** @var RedirectionCrumbs $crumbs */
    protected $crumbs;


    /**
     * RedirectionCrumbsSubscriber constructor.
     * @param RedirectionCrumbs $crumbs
     */
    public function __construct(RedirectionCrumbs $crumbs)
    {
//        $this->request = $request;
        $this->crumbs = $crumbs;
    }

    public function onKernelController(ControllerEvent $event)
    {
//        $controller = $event->getController();
//
//        // when a controller class defines multiple action methods, the controller
//        // is returned as [$controllerInstance, 'methodName']
//        if (is_array($controller)) {
//            $controller = $controller[0];
//        }
//
//        if ($controller instanceof CrumbedController) {
//            …
//        }

//        $redirect = $this->request->get('redirect');
//        if ( ! empty($redirect)) {
//            $this->crumbs->addCrumb($redirect);
//        }

    }

    public function onKernelRequest(RequestEvent $request_event)
    {
        $request = $request_event->getRequest();
        $redirect = $request->get('redirect');
//        if ($request->getMethod() == Request::METHOD_GET) {
        if ( ! empty($redirect)) {
            $this->crumbs->addCrumb($redirect);
        }
//        }

    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}