<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */


namespace ghiyam\apix\actions;


use ghiyam\apix\clients\ApiRequest;
use ghiyam\apix\clients\ApiResponse;
use ghiyam\apix\controllers\ServiceController;
use ghiyam\apix\query\Query;
use ghiyam\apix\query\QueueBuilder;
use yii\base\Action;

/**
 * Class FetchAction
 * Абстрактный класс действия запроса к API сервиса.
 * Результат выполнения унаследованного действия запроса обрабатывается, в дальнейшем, как запрос к API и,
 * соответственно, оно всегда должено возвращать массив параметров запроса. Полученный ответ обрабатывается
 * относительно параметра `responseType`, если не указано иное, то используется формат `json`.
 *
 * > Tip: Если не требуется обрабатывать запрос действия, то достаточно вернуть пустой массив.
 *
 * Пример результата выполнения:
 * ```
 *
 * [
 *      'method' => '<related_api_method>', // mandatory param
 *      'params' =>                         // optional param
 *          [
 *              // ... API method params goes here ...
 *          ]
 * ]
 *
 * ```
 *
 * @property-read ServiceController $controller
 *
 * @package ghiyam\apix\actions
 */
abstract class FetchAction extends Action
{

    /**
     * @var string
     */
    public $requestType = "GET";

    /**
     * @var bool
     */
    public $eventHandled = false;

    /**
     * @var mixed
     */
    public $parsedResult;

}