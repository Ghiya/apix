<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\controllers;


use ghiyam\apix\APIx;
use ghiyam\apix\query\Query;
use yii\base\Controller;

/**
 * Class ServiceController
 *
 * @property APIx $module
 *
 * @package ghiyam\apix\controllers
 */
abstract class ServiceController extends Controller
{


    /**
     * @var array
     */
    public $service = [];


    /**
     * @var array
     */
    public $routeRules = [];


    /**
     * @var bool
     */
    public $fetchResult = true;


    /**
     * @param Query $query
     *
     * @throws \ErrorException
     * @throws \ghiyam\apix\exceptions\ClientRequestException
     */
    public function sendQuery(Query &$query)
    {
        $this->module->getServiceWithParams($this->service)->sendRequest($query);
    }

}