<?php
namespace SDispatcher\Middleware;

use SDispatcher\DataResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ArrayToDataResponseListener implements EventSubscriberInterface
{
    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $e
     */
    public function onKernelView(GetResponseForControllerResultEvent $e)
    {
        $data = $e->getControllerResult();

        if (is_array($data)) {
            $e->setResponse(new DataResponse($data));
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
