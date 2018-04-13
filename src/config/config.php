<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */

return [
    'controllerMap' => [
        'default' => [
            // default class is abstract, use implementation instead
            'class' => 'ghiyam\apix\controller\ServiceController',
            'connection' => [
                'clientParams' => []
            ],
            'routeRules' => [],
        ]
    ],
    'components' => [
        'services' => [
            'class' => 'ghiyam\apix\Services',
        ],
    ],
];
