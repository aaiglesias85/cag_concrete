<?php

namespace App\Controller;

use App\Utils\ScriptService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class DefaultController extends AbstractController
{
    private $scriptService;

    public function __construct(ScriptService $scriptService)
    {
        $this->scriptService = $scriptService;
    }

    // test email
    public function testemail()
    {

        //Enviar email
        $direccion_url = $this->scriptService->ObtenerURL();
        $direccion_from = $this->getParameter('mailer_sender_address');
        $from_name = $this->getParameter('mailer_from_name');

        $asunto = "Test Email";
        $contenido = "Sending emails is fun again!";
        // symfony mailer
        $mensaje = (new TemplatedEmail())
            ->from(new Address($direccion_from, $from_name))
            ->to('cyborgmnk@gmail.com')
            ->subject($asunto)
            ->htmlTemplate('mailing/mail.html.twig')
            ->context([
                'direccion_url' => $direccion_url,
               'asunto' => $asunto,
               'receptor' => "Usuario",
               'contenido' => $contenido,
          ])
            // attach
            // ->attachFromPath('/path/to/documents/terms-of-use.pdf')
            // optionally you can tell email clients to display a custom name for the file
            // ->attachFromPath('/path/to/documents/privacy.pdf', 'Privacy Policy')
            // optionally you can provide an explicit MIME type (otherwise it's guessed)
            // ->attachFromPath('/path/to/documents/contract.doc', 'Contract', 'application/msword')
        ;

        $this->scriptService->mailer->send($mensaje);

        return new Response("OK", 200);
    }
}
