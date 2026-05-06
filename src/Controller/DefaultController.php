<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Service\ScriptService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserInterface;

class DefaultController extends AbstractController
{
    private $scriptService;

    public function __construct(ScriptService $scriptService)
    {
        $this->scriptService = $scriptService;
    }

    public function testemail(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return new Response('', 403);
        }

        $rawTo = $user instanceof Usuario ? $user->getEmail() : $user->getUserIdentifier();
        $toEmail = trim((string) $rawTo);
        if ('' === $toEmail || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return new Response('Email inválido', 400);
        }

        $direccion_url = $this->scriptService->ObtenerURL();
        $direccion_from = $this->getParameter('mailer_sender_address');
        $from_name = $this->getParameter('mailer_from_name');

        $asunto = 'Test Email';
        $contenido = 'Sending emails is fun again!';
        $receptor = ($user instanceof Usuario && $user->getNombre()) ? $user->getNombre() : 'Usuario';

        $mensaje = (new TemplatedEmail())
            ->from(new Address($direccion_from, $from_name))
            ->to($toEmail)
            ->subject($asunto)
            ->htmlTemplate('mailing/mail.html.twig')
            ->context([
                'direccion_url' => $direccion_url,
                'asunto' => $asunto,
                'receptor' => $receptor,
                'contenido' => $contenido,
            ]);

        $this->scriptService->mailer->send($mensaje);

        return new Response('OK', 200);
    }
}
