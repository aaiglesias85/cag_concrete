#!/opt/lampp/bin/php
<?php
$options = array(
    'login' => 'UsrTestServicios',
    'password' => 'U$$vr2$tS2T',
);

// $wsdl = 'http://qaws.ssichilexpress.cl/OSB/GenerarOTDigitalIndividualC2C?wsdl';
// el WSDL en linea esta entregando un host invalido (probablemente de QA interno) como endpoint
// asi que lo descargamos y lo usamos de forma local
$wsdl = 'GenerarOTDigitalIndividualC2C.wsdl';
$client = new SoapClient($wsdl, $options);

$args = array(
    'IntegracionAsistidaOp' => array(
        'reqGenerarIntegracionAsistida' => array(
            'codigoProducto' => 3,
            'codigoServicio' => 3,
            'comunaOrigen' => 'RENCA',
            'numeroTCC' => 22106942,
            'referenciaEnvio' => '123456789',
            'referenciaEnvio2' => 'Compra1',
            'eoc' => 0,
            'Remitente' => array(
                'nombre' => 'Mario Moyano',
                'email' => 'mmoyano@chilexpress.cl',
                'celular' => '84642291',
            ),
            'Destinatario' => array(
                'nombre' => 'Alexis Erazox',
                'email' => 'aerazo@chilexpress.cl',
                'celular' => '84642291',
            ),
            'Direccion' => array(
                'comuna' => 'PENALOLEN',
                'calle' => 'Camino de las Camelias',
                'numero' => '7909',
                'complemento' => 'Casa 33',
            ),
            'DireccionDevolucion' => array(
                'comuna' => 'PUDAHUEL',
                'calle' => 'Jose Joaquin Perez',
                'numero' => '1376',
                'complemento' => 'Piso 2',
            ),
            'Pieza' => array(
                'peso' => 5,
                'alto' => 1,
                'ancho' => 1,
                'largo' => 1,
            ),
        )
    )
);

$result = null;
try {
    $result = $client->IntegracionAsistidaOp($args['IntegracionAsistidaOp']);
    // $result = $client->__soapCall('IntegracionAsistidaOp', $args); // forma alternativa de llamar el metodo
    file_put_contents('cxp.jpg', $result->respGenerarIntegracionAsistida->DatosEtiqueta->imagenEtiqueta);
    $result->respGenerarIntegracionAsistida->DatosEtiqueta->imagenEtiqueta = 'guardada en cxp.jpg';
    print_r($result->respGenerarIntegracionAsistida->EstadoOperacion);
    print_r($result->respGenerarIntegracionAsistida->DatosOT);
    print_r($result->respGenerarIntegracionAsistida->DatosEtiqueta);
} catch (SoapFault $exception) {
    // echo $exception;
    echo $exception->faultstring . "\n";
}

