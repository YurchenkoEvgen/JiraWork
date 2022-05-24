<?php
// src/EventListener/RequestListener.php
namespace App\EventListener;

use App\DTO\Jira\ConnectionInfo;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class MyListener
{
    public function onKernelRequest(RequestEvent $event)
    {

        if ($event->getRequest()->getRequestUri() != '/auth' && mb_substr($event->getRequest()->getRequestUri(),0,2) != '/_') {
            $con = new ConnectionInfo($event->getRequest()->getSession());
            if (!$con->isValid()) {
                $event->getRequest()->getSession()->set('authredirecturi', $event->getRequest()->getPathInfo());

                $resp = new Response();
                $resp->headers->set('Location', '/auth');
                $resp->send();
            }
        }

    }

}
