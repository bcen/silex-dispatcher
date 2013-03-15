<?php
namespace SDispatcher\Middleware;

use SDispatcher\DataResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ArrayToDataResponseListener implements EventSubscriberInterface
{
    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $e
     */
    public function onKernelView(GetResponseForControllerResultEvent $e)
    {
        $response = $e->getControllerResult();

        if (is_array($response)) {
            $e->setResponse(new DataResponse($response));
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::VIEW => 'onKernelView');
    }
}
