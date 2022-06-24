<?php
return [
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret'    => env('PAYPAL_SCERET'),
        'settings'  => array(
            'mode'                   => 'sandbox',
            'http.ConnectionTimeOut' => 1000,
            'log.LogEnabled'         => true,
            'log.FileName'           => storage_path() . '/logs/paypal.log',
            'log.LogLevel'           => 'INFO'
        ),
    ],

    '2checkout' => [
//
    ]
];
