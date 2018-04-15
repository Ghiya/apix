<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\controller;


use ghiyam\apix\APIx;
use ghiyam\apix\query\Query;
use yii\base\ActionEvent;
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
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->on(
            self::EVENT_AFTER_ACTION,
            function (ActionEvent $event) {
                /** @var ServiceController $controller */
                $controller = $event->action->controller;
                $event->result = $controller->module->fetch($event->result);
                var_dump($event->result);
                die;
                $event->handled = true;
            }
        );
    }


    /**
     * @param Query $query
     *
     * @return Query|null
     * @throws \ghiyam\apix\exceptions\ClientRequestException
     */
    final public function sendQuery(Query $query)
    {
        return $this->module->getServiceWithParams($this->service)->sendQuery($query);
    }


}