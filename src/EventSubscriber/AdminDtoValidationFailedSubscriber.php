<?php

namespace App\EventSubscriber;

use App\Exception\Admin\AdminDtoValidationFailedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Convierte {@see AdminDtoValidationFailedException} en JSON 400 con el shape del panel admin.
 */
final class AdminDtoValidationFailedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onKernelException', 32]];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!$throwable instanceof AdminDtoValidationFailedException) {
            return;
        }

        $event->setResponse(new JsonResponse($throwable->getPayload(), Response::HTTP_BAD_REQUEST));
    }
}
