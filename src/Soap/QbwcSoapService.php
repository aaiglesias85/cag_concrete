<?php

// src/Soap/QbwcSoapService.php

namespace App\Soap;

use App\Utils\QbwcService;

class QbwcSoapService
{
    private QbwcService $qbwcService;

    public function __construct(QbwcService $qbwcService)
    {
        $this->qbwcService = $qbwcService;
    }

    public function authenticate($username, $password)
    {
        $this->qbwcService->writeLog("Intento de login: {$username}");

        $user = $this->qbwcService->AutenticarLogin($username, $password);
        if (!$user) {
            return ['', 'nvu'];
        }

        $ticket = bin2hex(random_bytes(16));
        $this->qbwcService->SalvarToken($user, $ticket);

        $this->qbwcService->writeLog("Login exitoso: {$username}, ticket: {$ticket}");

        // return [$ticket, ''];

        return new \SoapVar(
            '<authenticateResponse xmlns="http://developer.intuit.com/">
                    <authenticateResult>
                        <string>' . $ticket . '</string>
                        <string></string>
                    </authenticateResult>
                    </authenticateResponse>',
            XSD_ANYXML
        );
    }

    public function sendRequestXML($ticket, $companyFileName, $qbXMLCountry, $qbXMLMajorVers, $qbXMLMinorVers)
    {
        $session = $this->qbwcService->BuscarSesion($ticket);
        if (!$session) {
            return '';
        }

        $qbxml = $this->qbwcService->GenerarRequestQBXML();
        $this->qbwcService->writelog($qbxml);

        return $qbxml;
    }

    public function receiveResponseXML($ticket, $response, $hresult, $message)
    {
        $session = $this->qbwcService->BuscarSesion($ticket);
        if (!$session) {
            return 0;
        }

        $this->qbwcService->UpdateSyncQueueQbwc($response);
        return 100; // 100 = completado
    }

    public function getLastError($ticket)
    {
        return 'No error';
    }

    public function closeConnection($ticket)
    {
        $this->qbwcService->EliminarToken($ticket);
        return 'Conexión cerrada correctamente.';
    }

    public function serverVersion($version) { return '1.0'; }
    public function clientVersion($strVersion) { return ''; }
}
