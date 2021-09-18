<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */


namespace ghiyam\apix\controllers;


use ghiyam\apix\ApiService;
use ghiyam\apix\APIx;
use ghiyam\apix\exceptions\UnknownAPIException;
use yii\base\Controller;
use yii\base\InvalidRouteException;

/**
 * Class ServiceController
 *
 * @property-read APIx       $module
 * @property-read ApiService $apiService
 *
 * @package ghiyam\apix\controllers
 */
abstract class ServiceController extends Controller
{

    /**
     * @var string
     */
    public $title = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var array
     */
    public $service = [];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->attachBehavior('fetchAction', FetchActionBehaviour::class);
    }

    /**
     * @inheritDoc
     *
     * @throws UnknownAPIException
     */
    public function runAction($id, $params = [])
    {
        try {
            return parent::runAction($id, $params);
        } catch (InvalidRouteException $invalidRouteException) {
            throw new UnknownAPIException($this->id, $id, $invalidRouteException->getPrevious());
        }
    }

    /**
     * @return ApiService
     */
    public function getApiService(): ApiService
    {
        return $this->module->getService($this->service);
    }
}