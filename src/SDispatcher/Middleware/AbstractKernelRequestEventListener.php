<?php
namespace SDispatcher\Middleware;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * It provides a base class for implementing listener as an event subscriber
 * and Silex middleware listener.
 */
abstract class AbstractKernelRequestEventListener implements EventSubscriberInterface
{
    /**
     * Does the actual work of `__invoke` and `onKernelRequest`.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    abstract protected function doKernelRequest(Request $request);

    /**
     * Will be called on `KernelEvents::REQUEST`.
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $e
     */
    public function onKernelRequest(GetResponseEvent $e)
    {
        $ret = $this->doKernelRequest($e->getRequest());
        if ($ret instanceof Response) {
            $e->setResponse($ret);
        }
    }

    /**
     * Same as {@link onKernelRequest()}. But it will be used as a callback.
     * <code>
     * $app->before(new SubclassOfAbstractKernelRequestEventListener($routes));
     * // or standalone
     * $inspector = new SubclassOfAbstractKernelRequestEventListener($routes);
     * $inspector($request);
     * call_user_func($inspector, $request); // valid also
     * </code>
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request)
    {
        return $this->doKernelRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => array('onKernelRequest', 4));
    }
}
