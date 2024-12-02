<?php

namespace App\Listener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RedirectExceptionListener {

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $serviceContainer;

    function __construct($serviceContainer) {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @param $event
     */
    public function checkRedirect(ExceptionEvent $event) {
        $exception = $event->getThrowable();
        if ($exception instanceof NotFoundHttpException) {
            $response = new RedirectResponse($this->serviceContainer->get('router')->generate('error404'));
            $event->setResponse($response);
        }
    }

}
