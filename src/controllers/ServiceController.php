<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\controllers;


use ghiyam\apix\actions\FetchAction;
use ghiyam\apix\APIx;
use ghiyam\apix\Connector;
use ghiyam\apix\exceptions\UnknownAPIException;
use ghiyam\apix\query\Query;
use yii\base\ActionEvent;
use yii\base\Controller;
use yii\base\InvalidRouteException;

/**
 * Class ServiceController
 *
 * @property-read APIx $module
 * @property-read Connector $connector
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
     * @var array
     */
    public $routeRules = [];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->on(
            self::EVENT_AFTER_ACTION,
            function (ActionEvent $event) {
                if ($event->action instanceof FetchAction) {
                    /** @var FetchAction $fetchAction */
                    $fetchAction = $event->action;
                    $event->result = $fetchAction->fetchResult($event->result);
                    $event->handled = $fetchAction->eventHandled;
                }
            }
        );
    }


    /**
     * {@inheritdoc}
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
     * @param Query $query
     *
     * @throws \ErrorException
     * @throws \ghiyam\apix\exceptions\ClientRequestException
     */
    public function sendQuery(Query &$query)
    {
        $this->getConnector()->sendRequest($query);
    }

    /**
     * Возвращает объект соединения контроллера сервиса API.
     *
     * @return Connector
     */
    final public function getConnector()
    {
        return $this->module->getConnector($this->service);
    }

}