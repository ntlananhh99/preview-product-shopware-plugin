<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CustomCoreSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'setCustomXFrameOptions',
        ];
    }

    public function setCustomXFrameOptions(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    }
}
