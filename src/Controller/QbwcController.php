<?php

namespace App\Controller;

use App\Entity\UserQbwcToken;
use App\Utils\QbwcService;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class QbwcController extends AbstractController
{
    private QbwcService $qbwcService;

    public function __construct(QbwcService $qbwcService)
    {
        $this->qbwcService = $qbwcService;
    }

    public function qbwc(Request $request): Response
    {
        $xmlContent = $request->getContent();
        $this->qbwcService->writeLog("Solicitud recibida:\n" . $xmlContent);

        if (str_contains($xmlContent, 'authenticate')) {
            return $this->handleAuthenticate($xmlContent);
        } elseif (str_contains($xmlContent, 'sendRequestXML')) {
            return $this->handleSendRequestXML($xmlContent);
        } elseif (str_contains($xmlContent, 'receiveResponseXML')) {
            return $this->handleReceiveResponseXML($xmlContent);
        } elseif (str_contains($xmlContent, 'getLastError')) {
            return $this->handleGetLastError();
        } elseif (str_contains($xmlContent, 'closeConnection')) {
            return $this->handleCloseConnection($xmlContent);
        }

        return new Response($this->wrapSoapResponse('<unknownResponse>Unknown request</unknownResponse>'), 200, ['Content-Type' => 'text/xml']);
    }

    private function handleAuthenticate(string $xmlContent): Response
    {
        $xml = simplexml_load_string($xmlContent);
        $namespaces = $xml->getNamespaces(true);
        $body = $xml->children($namespaces['soap'])->Body;
        $authNode = $body->children($namespaces[''])->authenticate;

        $username = (string)$authNode->strUserName;
        $password = (string)$authNode->strPassword;

        $this->qbwcService->writeLog("Intento de login: {$username}");

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

        $ticket = bin2hex(random_bytes(16));
        $this->qbwcService->SalvarToken($user, $ticket);
        $this->qbwcService->writeLog("Login exitoso: {$username}, ticket: {$ticket}");

        $response = "<authenticateResponse xmlns=\"http://developer.intuit.com/\">
            <authenticateResult>
                <string>{$ticket}</string>
                <string></string>
            </authenticateResult>
        </authenticateResponse>";

        return new Response($this->wrapSoapResponse($response), 200, ['Content-Type' => 'text/xml']);
    }

    private function handleSendRequestXML(string $xmlContent): Response
    {
        $ticket = $this->extractTicket($xmlContent, 'sendRequestXML');
        $session = $this->qbwcService->getDoctrine()->getRepository(UserQbwcToken::class)
            ->BuscarToken($ticket);

        if (!$session) {
            return new Response($this->wrapSoapResponse('<sendRequestXMLResponse><sendRequestXMLResult></sendRequestXMLResult></sendRequestXMLResponse>'), 200, ['Content-Type' => 'text/xml']);
        }

        $qbxml = $this->qbwcService->GenerarRequestQBXML();
        if ($qbxml === '') {
            return new Response($this->wrapSoapResponse('<sendRequestXMLResponse><sendRequestXMLResult></sendRequestXMLResult></sendRequestXMLResponse>'), 200, ['Content-Type' => 'text/xml']);
        }

        $this->qbwcService->writeLog("Enviando QBXML para ticket: {$ticket}");
        $this->qbwcService->writeLog("XML generado: {$qbxml}");

        $response = "<sendRequestXMLResponse xmlns=\"http://developer.intuit.com/\">
            <sendRequestXMLResult>{$qbxml}</sendRequestXMLResult>
        </sendRequestXMLResponse>";

        return new Response($this->wrapSoapResponse($response), 200, ['Content-Type' => 'text/xml']);
    }

    private function handleReceiveResponseXML(string $xmlContent): Response
    {
        $ticket = $this->extractTicket($xmlContent, 'receiveResponseXML');

        $session = $this->qbwcService->getDoctrine()->getRepository(UserQbwcToken::class)
            ->BuscarToken($ticket);

        if (!$session) {
            return new Response($this->wrapSoapResponse('<receiveResponseXMLResponse><receiveResponseXMLResult>0</receiveResponseXMLResult></receiveResponseXMLResponse>'), 200, ['Content-Type' => 'text/xml']);
        }

        $this->qbwcService->writeLog("Recibiendo respuesta para ticket: {$ticket}");
        $this->qbwcService->UpdateSyncQueueQbwc($xmlContent);

        $response = "<receiveResponseXMLResponse xmlns=\"http://developer.intuit.com/\">
            <receiveResponseXMLResult>100</receiveResponseXMLResult>
        </receiveResponseXMLResponse>";

        return new Response($this->wrapSoapResponse($response), 200, ['Content-Type' => 'text/xml']);
    }

    private function handleGetLastError(): Response
    {
        $this->qbwcService->writeLog("Solicitud getLastError recibida");
        $response = "<getLastErrorResponse xmlns=\"http://developer.intuit.com/\">
            <getLastErrorResult>No error</getLastErrorResult>
        </getLastErrorResponse>";

        return new Response($this->wrapSoapResponse($response), 200, ['Content-Type' => 'text/xml']);
    }

    private function handleCloseConnection(string $xmlContent): Response
    {
        $ticket = $this->extractTicket($xmlContent, 'closeConnection');
        $this->qbwcService->EliminarToken($ticket);
        $this->qbwcService->writeLog("Cierre de sesión para ticket: {$ticket}");

        $response = "<closeConnectionResponse xmlns=\"http://developer.intuit.com/\">
            <closeConnectionResult>Conexión cerrada correctamente.</closeConnectionResult>
        </closeConnectionResponse>";

        return new Response($this->wrapSoapResponse($response), 200, ['Content-Type' => 'text/xml']);
    }

    private function extractTicket(string $xmlContent, string $function): ?string
    {
        $xml = simplexml_load_string($xmlContent);
        $namespaces = $xml->getNamespaces(true);
        $body = $xml->children($namespaces['soap'])->Body;
        $func = $body->children($namespaces[''])->{$function};

        return isset($func->ticket) ? (string)$func->ticket : null;
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
