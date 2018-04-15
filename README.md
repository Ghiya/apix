# APIx
Универсальный плагин для использования API внешних сервисов.

# Пример конфигурации приложения Yii2

```php


[
    // ...
    'modules'    =>
        [
            'apix' => [
                'class' => 'ghiyam\apix\APIx',
                'controllerMap' => [
                    // API service with REST client example
                    'some_vendor'    =>
                        [
                            // default controller class is abstract, use implementation instead
                            'class'      => 'ghiyam\apix\controller\ServiceController',
                            'service' =>
                                [
                                    'type'   => \ghiyam\apix\Service::TYPE_REST,
                                    'params' => [
                                        'credentials' => [],
                                        'url'         => 'localhost',
                                        'port'        => '80',
                                        'uri'         => 'path/to/api/root',
                                        'secured'     => false,
                                        'timeout'     => 3,
                                        'requestType' => \ghiyam\apix\client\RestClient::REQUEST_METHOD_GET
                                    ]
                                ],
                            'routeRules' =>
                                [
                                    // ... \yii\base\Model rules here ...
                                ],
                        ],
                    // API service with SOAP client example
                    'another_vendor' =>
                        [
                            // default controller class is abstract, use implementation instead
                            'class'      => 'ghiyam\apix\controller\ServiceController',
                            'service' =>
                                [
                                    'type'   => \ghiyam\apix\Service::TYPE_SOAP,
                                    'params' => [
                                        'credentials' => [],
                                        'namespaces'  =>
                                            [
                                                'header'   => '',
                                                'envelope' => '',
                                            ],
                                        'soap'        => [
                                            'location'     => '',
                                            'uri'          => '',
                                            'trace'        => true,
                                            'compression'  => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                                            'exceptions'   => false,
                                            'soap_version' => SOAP_1_1,
                                            'encoding'     => 'UTF-8',
                                        ],
                                    ]
                                ],
                            'routeRules' =>
                                [
                                    // ... \yii\base\Model rules here ...
                                ],
                        ]
                    // ... any other REST/SOAP API services ...
                ],
            ],
        ],
    //...
]


```