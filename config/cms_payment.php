<?php

return [
    'decimal' => env("PAYMENT_DECIMAL", 2),
    'currency' => env("PAYMENT_CURRENCY", 'CNY'),
    'route_prefix' => env("PAYMENT_ROUTE_PREFIX", '/payment'),
    'time_out' => env("PAYMENT_TIME_OUT", 15),
    'channel' => [
        'wechat' => [
            'app_id' => 'wx5b3664566333a336',
            'mch_id' => '1648035474',
            'secret_key' => '64ef8510e0a17f3cbb8c96ec16bd61fa',
            'private_key' => storage_path('/certs/apiclient_key.pem'),
            'certificate' => storage_path('/certs/apiclient_cert.pem'),
            'http' => [
                'throw' => false,
                'timeout' => 5.0,

                'retry' => true,
            ],
        ],
        'alipay' => [
            'appid' => '2019022663440152',
            'op_app_id' => '2014072300007148', //小程序APPID
            'merchant_key' => 'MIIEvQIBADANB',
            'public_key' => 'MIIBIjANBg'
        ]
    ]
];