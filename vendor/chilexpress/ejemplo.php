<?php

try {
    $WSDL = 'http://testservices.wschilexpress.com/GeoReferencia?WSDL';
    $client = new SoapClient($WSDL);

    /* HEADER */
    $ns = 'http://www.chilexpress.cl/CorpGR/';

    $headerbody = array('transaccion' => array('fechaHora' => '2015-02-18T14:51:00',
        'idTransaccionNegocio' => '0',
        'sistema' => 'TEST',
        'usuario' => 'TEST'
    )
    );

    //Create Soap Header.
    $header = new SOAPHeader($ns, 'headerRequest', $headerbody);

    //set the Headers of Soap Client.
    $client->__setSoapHeaders($header);

    /* BODY */


    $result = $client->__soapCall('ConsultarRegiones', array(
            "ConsultarRegiones" => array('reqObtenerRegion' => '')
        )
        , array(), null, $outputHeaders);

    echo "<pre>";

    print_r($result);
    echo "</pre>";
} catch (SoapFault $e) {
    echo "ERRORRRRRR !!!!!! \n";
    print_r($e);
}
?>