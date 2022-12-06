<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event)
    {
        // bad request
        if ($event->getThrowable() instanceof HttpExceptionInterface) {
            $event->setResponse(new JsonResponse([
                'code' => $event->getThrowable()->getStatusCode(),
                'message' => $event->getThrowable()->getMessage(),
            ], $event->getThrowable()->getStatusCode()));
        } else {
//            // internal server error
//            $event->setResponse(new JsonResponse([
//                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
//                'message' => Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
//            ], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
