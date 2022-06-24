<?php
return [
    'paypal' => [
        'client_id' => 'ARwMa5uyetqIQvcjlCrbduEsdzKPxL-xPbG6TtlwntHO2JxIucH8trFiw1NKOflsEXeNF1zABXppwJzv',
        'secret'    => 'EIkRvnOwKwAI5EvmvYao1kWEPIIYzBv6hIzthew5XfmSSgfjID-D0BCaNbgaq_SfFhncbduVX5XS1en8',
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
