<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\controller;


use ghiyam\apix\Connection;
use ghiyam\apix\Services;
use yii\base\Controller;

abstract class ServiceController extends Controller
{


    /**
     * @var array
     */
    public $connection = [];


    /**
     * @var array
     */
    public $routeRules = [];


    /**
     * @param \yii\base\Action $action
     * @param mixed            $result
     *
     * @return mixed
     * @throws \ErrorException
     * @throws \ghiyam\apix\exceptions\ClientRequestException
     * @throws \yii\base\InvalidConfigException
     */
    public function afterAction($action, $result)
    {
        return parent::afterAction($action, $this->getServices()->fetch($result));
    }


    /**
     * @return null|object|Services
     * @throws \yii\base\InvalidConfigException
     */
    protected function getServices()
    {
        /** @var Services $services */
        $services = \Yii::$app->get('services');
        $services->connection = new Connection($this->connection);
        return $services;
    }

}