<?php

namespace App\Soap;

use App\Utils\QbwcService;

class QbwcSoapService
{
    private QbwcService $qbwcService;

    public function __construct(QbwcService $qbwcService)
    {
        $this->qbwcService = $qbwcService;
    }

    public function authenticate($params)
    {
        $username = $params->strUserName ?? '';
        $password = $params->strPassword ?? '';

        $this->qbwcService->writeLog("Intento de login: {$username}");

        $user = $this->qbwcService->AutenticarLogin($username, $password);
        if (!$user) {
            return [
                'authenticateResult' => [
                    'string' => ['', 'nvu']
                ]
            ];
        }

        $ticket = bin2hex(random_bytes(16));
        $this->qbwcService->SalvarToken($user, $ticket);

        $this->qbwcService->writeLog("Login exitoso: {$username}, ticket: {$ticket}");

        return [
            'authenticateResult' => [
                'string' => [
                    $ticket, // o '' si no hay acciones
                    ''       // o 'nvu' si no autorizado
                ]
            ]
        ];

        /*
        return new \SoapVar(
            '<authenticateResponse xmlns="http://developer.intuit.com/">
                    <authenticateResult>
                        <string>' . $ticket . '</string>
                        <string></string>
                    </authenticateResult>
                    </authenticateResponse>',
            XSD_ANYXML
        );
        */
    }

    public function sendRequestXML($params)
    {
        $ticket = $params->ticket ?? '';
        $companyFileName = $params->strCompanyFileName ?? '';
        $qbXMLCountry = $params->qbXMLCountry ?? '';
        $qbXMLMajorVers = $params->qbXMLMajorVers ?? 0;
        $qbXMLMinorVers = $params->qbXMLMinorVers ?? 0;

        $this->qbwcService->writeLog("handleSendRequestXML ticket: {$ticket}");

        $session = $this->qbwcService->BuscarSesion($ticket);
        if (!$session) {
            $this->qbwcService->writeLog("handleSendRequestXML No hay sesión");
            return '';
        }

        $qbxml = $this->qbwcService->GenerarRequestQBXML();
        $this->qbwcService->writelog($qbxml);

        if (empty($qbxml)) {
            return '';
        }

        return ['sendRequestXMLResult' => $qbxml];

        /*
        return new \SoapVar(
            "<sendRequestXMLResponse><sendRequestXMLResult>{$qbxml}</sendRequestXMLResult></sendRequestXMLResponse>",
            XSD_ANYXML
        );
        */
    }

    public function receiveResponseXML($params)
    {
        $ticket = $params->ticket ?? '';
        $response = $params->response ?? '';
        $hresult = $params->hresult ?? '';
        $message = $params->message ?? '';

        $this->qbwcService->writeLog("Recibiendo respuesta para ticket: {$ticket}");

        $session = $this->qbwcService->BuscarSesion($ticket);
        if (!$session) {
            return ['receiveResponseXMLResult' => 0];
        }

        $this->qbwcService->UpdateSyncQueueQbwc($response);

        return ['receiveResponseXMLResult' => 100];
    }

    public function getLastError($params)
    {
        $ticket = $params->ticket ?? '';
        $this->qbwcService->writeLog("Solicitud getLastError recibida para ticket: {$ticket}");

        return ['getLastErrorResult' => 'No error'];
    }

    public function closeConnection($params)
    {
        $ticket = $params->ticket ?? '';
        $this->qbwcService->EliminarToken($ticket);
        $this->qbwcService->writeLog("Cierre de sesión para ticket: {$ticket}");

        return ['closeConnectionResult' => 'Conexión cerrada correctamente.'];
    }

    public function serverVersion($params)
    {
        $version = $params->strVersion ?? '';
        return ['serverVersionResult' => '1.0.0'];
    }

    public function clientVersion($params)
    {
        $strVersion = $params->strVersion ?? '';
        return ['clientVersionResult' => ''];
    }
}