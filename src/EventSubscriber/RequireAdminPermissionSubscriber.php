<?php

namespace App\EventSubscriber;

use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Aplica #[RequireAdminPermission] sin repetir exigirUsuarioOlogin + permiso en cada método.
 */
final class RequireAdminPermissionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AdminAccessService $adminAccess,
        private readonly Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => ['onKernelController', 16]];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        if (!\is_array($controller) || !isset($controller[0], $controller[1]) || !\is_object($controller[0])) {
            return;
        }

        [$object, $method] = $controller;
        $reflection = new \ReflectionClass($object);
        if (!$reflection->hasMethod($method)) {
            return;
        }

        $refMethod = $reflection->getMethod($method);
        foreach ($refMethod->getAttributes(RequireAdminPermission::class) as $attr) {
            /** @var RequireAdminPermission $spec */
            $spec = $attr->newInstance();
            $result = $this->adminAccess->requirePermission(
                $this->security->getUser(),
                $spec->functionId,
                $spec->permission
            );
            if ($result instanceof RedirectResponse) {
                if ($spec->jsonOnDenied) {
                    $user = $this->security->getUser();
                    $status = null === $user ? Response::HTTP_UNAUTHORIZED : Response::HTTP_FORBIDDEN;
                    $event->setController(static fn () => new JsonResponse(
                        ['success' => false, 'error' => 'Access denied'],
                        $status
                    ));
                } else {
                    $event->setController(static fn () => $result);
                }
            }

            return;
        }
    }
}
