<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */


namespace ghiyam\apix\controllers;


use ghiyam\apix\actions\FetchAction;
use ghiyam\apix\ApiService;
use ghiyam\apix\APIx;
use ghiyam\apix\exceptions\ApiClientFetchException;
use ghiyam\apix\exceptions\UnknownAPIException;
use yii\base\ActionEvent;
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
        $this->on(
            self::EVENT_AFTER_ACTION,
            function (ActionEvent $event) {
                if ($event->action instanceof FetchAction) {
                    $event->result = $this->runFetchAction($event->action, $event->result);
                    $event->handled = $event->action->eventHandled;
                }
            }
        );
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
     * @param FetchAction $fetchAction
     * @param array       $actionResult
     * @return array|bool|mixed
     * @throws ApiClientFetchException
     */
    protected function runFetchAction(FetchAction $fetchAction, array $actionResult)
    {
        return $this->getApiService()->send($actionResult, $fetchAction->requestType);
    }

    /**
     * @return ApiService
     */
    public function getApiService(): ApiService
    {
        return $this->module->getService($this->service);
    }
}