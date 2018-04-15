<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\controller;


use ghiyam\apix\Service;
use ghiyam\apix\APIx;
use yii\base\Controller;

/**
 * Class ServiceController
 *
 * @property APIx $module
 *
 * @package ghiyam\apix\controller
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
        return parent::afterAction($action, $this->module->fetch($result, $this->service));
    }


    /**
     * @return null|object|APIx
     * @throws \yii\base\InvalidConfigException
     */
    /*protected function getServices()
    {
        $services = \Yii::$app->get('services');
        $services->connection = new Service($this->service);
        return $services;
    }*/

}