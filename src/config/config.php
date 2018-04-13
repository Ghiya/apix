<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */

/**
 * Пример конфигурации yii2 приложения для использования плагина APIx.
 */
return [
    'controllerMap' => [
        // REST connection config example
        'some_vendor'    =>
            [
                // default class is abstract, use implementation instead
                'class'      => 'ghiyam\apix\controller\ServiceController',
                'connection' =>
                    [
                        'type'   => \ghiyam\apix\Connection::TYPE_REST,
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
        // SOAP connection config example
        'another_vendor' =>
            [
                // default class is abstract, use implementation instead
                'class'      => 'ghiyam\apix\controller\ServiceController',
                'connection' =>
                    [
                        'type'   => \ghiyam\apix\Connection::TYPE_SOAP,
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
    ],
    'components'    =>
        [
            'services' => [
                'class' => 'ghiyam\apix\Services',
            ],
        ],
];
