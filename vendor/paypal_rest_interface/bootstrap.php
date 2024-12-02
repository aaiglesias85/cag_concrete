<?php

// Include the composer autoloader
if(!file_exists(__DIR__ .'/SDK/autoload.php')) {
    echo "The 'vendor' folder is missing. You must run 'composer update' to resolve application dependencies.\nPlease see the README for more information.\n";
    exit(1);
}

require_once __DIR__ . '/SDK/autoload.php';


use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

return getApiContext();

// SDK Configuration
function getApiContext()
{

    $clientId = $clientSecret = $mode = null;

    if(defined('PP_DEBUG'))
    {
        //$clientId       = 'AZGpvUh1ktPNl47Y7ykD7OG74iA7hbni2U6Gk30_ssyhSTN1s9iwYi39KT1fO3EiVBOBNdHwytNZgTPk';
        //$clientSecret   = 'EFWTXkElZvpl0IZC1A8cAQiskGQw3KQ67lT7uodyTJsSUr0moCbaHtd-fgYfd0xfyH7jjch9l4cfyDah';

        $clientId       = 'AeDVkBrmtKD49Iu7q7Xq9Rto8qivT80K96T6unK0iqO0s_T5SCHl5qMfRjxejNiGzTMA9PtgRN1i1XFX';
        $clientSecret   = 'EHqzG5SRSiOSW_g9sPusHKE60AjJAavQ9Kvn27ZuEHKuso5p3LQZa6SzXzBAj_CiTS8zXPWylOlbVBCT';
        $mode           = 'sandbox';
    }
    else
    {
        $clientId       = 'Ae-dHTiIOZSKMLXx8p56UlL-4qhBFYCRr8XLwkc17T96EHd_j3-53Mqc4zFfFSOaOswBMrvFKIj824ve';
        $clientSecret   = 'EMEjGjytuHz7Yjp-oHWYKBYmGet4BPLiBKPNo5nOq5KG0ilw8bXXeOpQ0Xq0CSf4TA5Hrtld5Cnrw6xp';
        $mode           = 'live';
    }

    $apiContext = new ApiContext(new OAuthTokenCredential(
        $clientId,
        $clientSecret
    ));

    $config = array(
        'http.ConnectionTimeOut' => 30,
        'http.Retry' => 1,
        'mode' => $mode,
        'log.LogEnabled' => true,
        'log.FileName' => '../PayPal.log',
        'log.LogLevel' => 'INFO'
    );

    if(defined('PP_PROXY'))
        $config['http.Proxy'] = PP_PROXY;

    $apiContext->setConfig($config);

    return $apiContext;
}