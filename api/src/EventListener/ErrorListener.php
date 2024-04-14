<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\HttpFoundation\Response;

class ErrorListener
{
    public function onJWTExpired(JWTExpiredEvent $event): void
    {
        $this->errorMessage($event);
    }

    public function onJWTInvalid(JWTInvalidEvent $event): void
    {
        $this->errorMessage($event);
    }

    public function onJWTNotFound(JWTNotFoundEvent $event): void
    {
        $this->errorMessage($event);
    }

    public function errorMessage(object &$event): void
    {
        $response = new Response(status: 401);

        $event->setResponse($response);
    }
}
