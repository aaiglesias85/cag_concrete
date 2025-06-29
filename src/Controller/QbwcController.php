<?php

namespace App\Controller;

use App\Soap\QbwcSoapService;
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

    // ruta para generar el xml de configuracion
    public function config(): Response
    {
        $name = 'Symfony QuickBooks Integration';
        $description = 'Symfony QuickBooks Integration';

        $host = $this->qbwcService->ObtenerURL();
        $appurl = $host . 'qbwc';
        $appsupport = $appurl;

        $username = 'admin@concrete.com';

        $fileid = \QuickBooks_WebConnector_QWC::fileID();
        $ownerid = \QuickBooks_WebConnector_QWC::ownerID();

        $qbtype = QUICKBOOKS_TYPE_QBFS;
        $readonly = false;
        $run_every_n_seconds = 300; // 5 min

        $qwc = new \QuickBooks_WebConnector_QWC(
            $name,
            $description,
            $appurl,
            $appsupport,
            $username,
            $fileid,
            $ownerid,
            $qbtype,
            $readonly,
            $run_every_n_seconds
        );

        $xml = $qwc->generate();

        return new Response($xml, 200, ['Content-Type' => 'text/xml']);
    }

    // ruta para exponer el servidor soap
    public function qbwc(Request $request): Response
    {
        try {

            $options = [
                'uri' => $this->qbwcService->ObtenerURL() . 'qbwc',
                'encoding' => 'UTF-8'
            ];

            $soapServer = new \SoapServer(null, $options);
            $soapServer->setObject(new QbwcSoapService($this->qbwcService));

            ob_start();
            $soapServer->handle();
            $response = ob_get_clean();

            return new Response($response, 200, ['Content-Type' => 'text/xml']);

        } catch (\Exception $e) {
            $this->qbwcService->writelog($e->getMessage(), 'errorlog.txt');

            return new Response($this->wrapSoapResponse('<unknownResponse>Unknown request</unknownResponse>'), 200, ['Content-Type' => 'text/xml']);
        }

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
