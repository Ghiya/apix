# APIx

![2.0.4](https://img.shields.io/static/v1?label=latest&message=2.0.4&color=006E90&style=flat-square)

Универсальный плагин динамических клиент-серверных запросов к API внешних сервисов в
приложениях [Yii2 framework](https://www.yiiframework.com/).

## Как это работает?

Плагин `APIx` является [модулем](https://www.yiiframework.com/doc/guide/2.0/en/structure-modules) и отвечает при
обработке запросов за конфигурацию сервисов API и соответствующий роутинг. Каждый сервис
представлен [контроллером](https://www.yiiframework.com/doc/guide/2.0/en/structure-controllers) и
его [действия](https://www.yiiframework.com/doc/guide/2.0/en/structure-controllers#actions) определяют запросы к API
этого сервиса. Плагин содержит объекты интегрированных клиентов для cURL/SOAP/SMPP соединений с API, которые
предполагается наследовать в конкретных реализациях.

## Конфигурация сервисов

Каждый из сервисов API представляется соответствующим контроллером, поэтому все используемые сервисы конфигурируются в
параметре `controllerMap` модуля плагина. Действие контроллера должно возвращать массив параметров запроса/запросов в
формате указанном ниже.

> Note: Для корректной работы контроллера сервиса API требуется унаследовать его от [\ghiyam\apix\controllers\ServiceController].

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
                            // default controller class is abstract, use inheritance instead
                            'class'      => 'ghiyam\apix\controller\ServiceController',
                            'service' =>
                                [
                                    'client' => [
                                        // default client class is abstract, use inheritance instead
                                        'class'         => '\ghiyam\apix\clients\CurlApiClient',
                                        'credentials'   => [],
                                        'clientOptions' => [
                                            'host'    => 'someHost',
                                            'port'    => 443,
                                            'uri'     => 'path/to/api/uri',
                                            'timeout' => 3,
                                        ]
                                    ],
                                ],
                        ],
                    // API service with SOAP client example
                    'another_vendor' =>
                        [
                            // default controller class is abstract, use implementation instead
                            'class'      => 'ghiyam\apix\controller\ServiceController',
                            'service' =>
                                [
                                    'client' => [
                                        // default client class is abstract, use inheritance instead
                                        'class'       => '\ghiyam\apix\clients\SoapApiClient',
                                        'credentials' => [],
                                        'namespaces'  =>
                                            [
                                                'header'   => '',
                                                'envelope' => '',
                                            ],
                                        'clientOptions' => [
                                            'location'     => '',
                                            'uri'          => '',
                                            'trace'        => true,
                                            'compression'  => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                                            'exceptions'   => false,
                                            'soap_version' => SOAP_1_1,
                                            'encoding'     => 'UTF-8',
                                        ],
                                    ],
                                ],
                        ]
                    // ... any other API clients implementations...
                ],
            ],
        ],
    //...
]


```

## Построение запросов

Каждый запрос должен содержать обязательный параметр `method` и необязательные параметры `params`. Первый содержит
название метода в обращении к сервису API, второй - его параметры.

Пример построения запроса

```

[
    'method' => '<api_method_name>',
    'params' =>
        [
            '<param_name>' => '<param_value>'
            // ... API method params here ...
        ]
]

```