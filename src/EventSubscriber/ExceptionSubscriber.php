<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // get code and message
        $message = $event->getThrowable()->getMessage();
        // if message is not json, then convert it to json
        if (json_decode($message) === null) {
            $message = json_encode(['message' => $message]);
        }
        $message = json_decode($message, true);
        if ($event->getThrowable() instanceof HttpExceptionInterface) {
            $event->setResponse(new JsonResponse([
                'code' => $event->getThrowable()->getStatusCode(),
                'errors' => $message,
            ], $event->getThrowable()->getStatusCode()));
        } else {
            // internal server error
            $event->setResponse(new JsonResponse([
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
            ], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
}
