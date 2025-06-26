<?php

namespace App\Controller;

use App\Entity\UserQbwcToken;
use App\Utils\QbwcService;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class QbwcController extends AbstractController
{

    private $qbwcService;

    public function __construct(QbwcService $qbwcService)
    {
        $this->qbwcService = $qbwcService;
    }

    public function qbwc(Request $request)
    {
        $xml = $request->getContent();

        $this->qbwcService->writeLog(var_export($xml, true));

        if (str_contains($xml, 'authenticate')) {
            return $this->handleAuthenticate($request);
        } elseif (str_contains($xml, 'sendRequestXML')) {
            return $this->handleSendRequestXML($request);
        } elseif (str_contains($xml, 'receiveResponseXML')) {
            return $this->handleReceiveResponseXML($request);
        } elseif (str_contains($xml, 'getLastError')) {
            return $this->handleGetLastError($request);
        } elseif (str_contains($xml, 'closeConnection')) {
            return $this->handleCloseConnection($request);
        }

        return new Response($this->wrapSoapResponse('<unknownResponse>Unknown request</unknownResponse>'), 200, ['Content-Type' => 'text/xml']);
    }


    private function handleAuthenticate(Request $request): Response
    {
        $xml = simplexml_load_string($request->getContent());
        $body = $xml->children('soap', true)->Body;
        $username = (string)$body->authenticate->strUserName;
        $password = (string)$body->authenticate->strPassword;

        $this->qbwcService->writeLog("Intento de login: {$username}");

        // autenticar
        $user = $this->qbwcService->AutenticarLogin($username, $password);

        if (!$user) {

            $this->qbwcService->writeLog("Login fallido para: {$username}");

            $response = '<authenticateResponse xmlns="http://developer.intuit.com/">
                <authenticateResult>
                    <string></string>
                    <string>nvu</string>
                </authenticateResult>
            </authenticateResponse>';
            return new Response($this->wrapSoapResponse($response), 200, ['Content-Type' => 'text/xml']);
        }

        // generar token
        $ticket = bin2hex(random_bytes(16));

        // salvar token
        $this->qbwcService->SalvarToken($user, $ticket);

        $this->qbwcService->writeLog("Login exitoso para: {$username}, ticket: {$ticket}");

        $response = "<authenticateResponse xmlns=\"http://developer.intuit.com/\">
            <authenticateResult>
                <string>{$ticket}</string>
                <string></string>
            </authenticateResult>
        </authenticateResponse>";

        return new Response($this->wrapSoapResponse($response), 200, ['Content-Type' => 'text/xml']);
    }

    private function handleSendRequestXML(Request $request): Response
    {
        $ticket = $this->extractTicket($request, 'sendRequestXML');

        // buscar token
        $session = $this->qbwcService->getDoctrine()->getRepository(UserQbwcToken::class)
            ->BuscarToken($ticket);
        if (!$session) {
            return new Response($this->wrapSoapResponse('<sendRequestXMLResponse><sendRequestXMLResult></sendRequestXMLResult></sendRequestXMLResponse>'), 200, ['Content-Type' => 'text/xml']);
        }

        // generar el xml
        $qbxml = $this->qbwcService->GenerarRequestQBXML();
        if ($qbxml == "") {
            return new Response($this->wrapSoapResponse('<sendRequestXMLResponse><sendRequestXMLResult></sendRequestXMLResult></sendRequestXMLResponse>'), 200, ['Content-Type' => 'text/xml']);
        }

        $this->qbwcService->writeLog("sendRequestXML con ticket: {$ticket}");
        $this->qbwcService->writeLog("XML enviado: " . $qbxml);

        $response = "<sendRequestXMLResponse xmlns=\"http://developer.intuit.com/\">
            <sendRequestXMLResult>{$qbxml}</sendRequestXMLResult>
        </sendRequestXMLResponse>";

        return new Response($this->wrapSoapResponse($response), 200, ['Content-Type' => 'text/xml']);
    }

    private function handleReceiveResponseXML(Request $request): Response
    {
        $ticket = $this->extractTicket($request, 'receiveResponseXML');

        // buscar token
        $session = $this->qbwcService->getDoctrine()->getRepository(UserQbwcToken::class)
            ->BuscarToken($ticket);
        if (!$session) {
            return new Response($this->wrapSoapResponse('<receiveResponseXMLResponse><receiveResponseXMLResult>0</receiveResponseXMLResult></receiveResponseXMLResponse>'), 200, ['Content-Type' => 'text/xml']);
        }

        // actualizar el estado
        $this->qbwcService->UpdateSyncQueueQbwc($request->getContent());

        $response = "<receiveResponseXMLResponse xmlns=\"http://developer.intuit.com/\">
            <receiveResponseXMLResult>100</receiveResponseXMLResult>
        </receiveResponseXMLResponse>";

        return new Response($this->wrapSoapResponse($response), 200, ['Content-Type' => 'text/xml']);
    }

    private function handleGetLastError(Request $request): Response
    {
        $response = "<getLastErrorResponse xmlns=\"http://developer.intuit.com/\">
            <getLastErrorResult>No error</getLastErrorResult>
        </getLastErrorResponse>";

        return new Response($this->wrapSoapResponse($response), 200, ['Content-Type' => 'text/xml']);
    }

    private function handleCloseConnection(Request $request): Response
    {
        $ticket = $this->extractTicket($request, 'closeConnection');

        // eliminar token
        $this->qbwcService->EliminarToken($ticket);

        $response = "<closeConnectionResponse xmlns=\"http://developer.intuit.com/\">
            <closeConnectionResult>Conexión cerrada correctamente.</closeConnectionResult>
        </closeConnectionResponse>";

        return new Response($this->wrapSoapResponse($response), 200, ['Content-Type' => 'text/xml']);
    }

    private function extractTicket(Request $request, string $function): ?string
    {
        $xml = simplexml_load_string($request->getContent());
        $body = $xml->children('soap', true)->Body;
        return (string)$body->{$function}->ticket ?? null;
    }

    private function wrapSoapResponse(string $body): string
    {
        return "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                <soap:Envelope xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">
                <soap:Body>
                {$body}
                </soap:Body>
                </soap:Envelope>";
    }


}
