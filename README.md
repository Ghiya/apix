# APIx
Универсальный плагин для использования API внешних сервисов.

## Возможности

- поддержка REST/SOAP типов взаимодействия с сервисом
- одновременное использование нескольких сервисов API
- настройки сервисов согласно стандартной конфигурации компонентов приложения Yii2
- построение составных запросов с использованием промежуточных результатов
- настройки роутинга сервисов в зависимости от параметров запроса


## Конфигурация сервисов / config

Каждый из сервисов API представляется соответствующим контроллером, поэтому все используемые сервисы конфигурируются в параметре `controllerMap` модуля плагина.
Действие контроллера должно возвращать массив параметров запроса/запросов [в формате указанном ниже](#queries).

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


## Построение запросов / queries

Каждый запрос должен содержать обязательный параметр `method` и необязательные параметры `params`. Первый содержит название метода в обращении к сервису API, второй - его параметры.
Дополнительно каждый запрос может определять служебный параметр `join`, согласно которым он определяется как *обычный* или *составной*.

### Построение обычных запросов

Пример построения обычного запроса


```

[
    'method' => '<api_method_name>',
    'params' =>
        [
            // ... API method params here ...
            '<param_name>' => '<param_value>'
        ]
]

```

### Построение составных запросов

Для указания объединяющего параметра используется специальный формат значения в присоединяемом запросе.
Если требуется значение параметра из ответа для присоединяемого запроса, то указывается его название в формате `|<params_name|`.
Если же требуется использование ответа целиком, то применяется формат `|*|`.

Пример построения составного запроса

```

[
    'method' => '<api_method_name>',
    'params' =>
        [
            // ... API method params here ...
            '<param_name>' => '<param_value>'
        ],
    'join' =>
        [
            [
                'method' => '<joined_api_method_name>',
                'params' =>
                    [
                        '<param_name>' => '<|<joined_param_from_response>|>'
                        // ... other API method params here ...
                        '<param_name>' => '<param_value>'
                    ],
                'join' =>
                    [
                        [
                            'method' => '<joined_api_method_name>',
                            'params' =>
                                [
                                    // use the whole response instead of one parameter from it
                                    '<param_name>' => '<|*|>'
                                    // ... other API method params here ...
                                    '<param_name>' => '<param_value>'
                                ],
                        ],
                    ]
            ],
            [
                'method' => '<joined_api_method_name>',
                'params' =>
                    [
                        '<param_name>' => '<|<joined_param_from_response>|>'
                        // ... other API method params here ...
                        '<param_name>' => '<param_value>'
                    ],
            ],
        ]
]

```

> Note: При обработке составных запросов формат результирующего ответа определяется форматом результата первого из простых запросов.