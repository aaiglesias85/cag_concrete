<?php
/**
 * Clase para servicios chilexpress
 */
Class ChileExpress
{
    function sucursalesDespachoDeRegion($codigoRegion = "RM")
    {
        try {
            $client_options = array(
                'login' => "UsrTester",
                'password' => "&8vhk8790|",
                'cache_wsdl' => 0,
                'exceptions' => 0,
                'stream_context' => stream_context_create(array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true //can fiddle with this one.
                    )
                ))
            );

            $WSDL = "http://testservices.wschilexpress.com/GeoReferencia?wsdl";
            $client = new SoapClient($WSDL, $client_options);

            $ns = 'http://www.chilexpress.cl/CorpGR/';
            $headerRequest = array(
                'transaccion' => array(
                    'fechaHora' => date('Y-m-d\TH:i:s.Z\Z', time()),
                    'idTransaccionNegocio' => '0',
                    'sistema' => 'TEST',
                    'usuario' => 'TEST'
                )
            );

            $header = new SoapHeader($ns, 'headerRequest', $headerRequest);
            $client->__setSoapHeaders($header);

            $result = $client->__soapCall('ConsultarOficinas_REG', array(
                    "ConsultarOficinas_REG" =>
                        array('reqObtenerOficinas_REG' =>
                            array(
                                'CodRegion' => $codigoRegion
                            )
                        )
                )
                , array(), null, $outputHeaders);

            $resultado = $result->respObtenerOficinas_REG;
            return $resultado;

        } catch (SoapFault $e) {
            return "Error \n";
            $msj = $e->getMessage();
            return "<pre>" . var_dump($e) . "</pre>";
        }
    }

    function sucursalesDespachoDeComuna($nombreComuna = "Santiago Centro")
    {
        try {
            $client_options = array(
                'login' => "UsrTester",
                'password' => "&8vhk8790|",
                'cache_wsdl' => 0,
                'exceptions' => 0,
                'stream_context' => stream_context_create(array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true //can fiddle with this one.
                    )
                ))
            );

            $WSDL = "http://testservices.wschilexpress.com/GeoReferencia?wsdl";
            $client = new SoapClient($WSDL, $client_options);

            $ns = 'http://www.chilexpress.cl/CorpGR/';
            $headerRequest = array(
                'transaccion' => array(
                    'fechaHora' => date('Y-m-d\TH:i:s.Z\Z', time()),
                    'idTransaccionNegocio' => '0',
                    'sistema' => 'TEST',
                    'usuario' => 'TEST'
                )
            );

            $header = new SoapHeader($ns, 'headerRequest', $headerRequest);
            $client->__setSoapHeaders($header);

            $result = $client->__soapCall('ConsultarOficinas', array(
                    "ConsultarOficinas" =>
                        array('reqObtenerOficinas' =>
                            array(
                                'GlsComuna' => $nombreComuna
                            )
                        )
                )
                , array(), null, $outputHeaders);


            $resultado = $result->respObtenerOficinas;
            return $resultado;

        } catch (SoapFault $e) {

            $msj = $e->getMessage();
            return "Error $msj\n";
            return "<pre>" . var_dump($e) . "</pre>";
        }
    }
}

