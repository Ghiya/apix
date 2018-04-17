<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\controllers;


use ghiyam\apix\APIx;
use ghiyam\apix\query\Query;
use ghiyam\apix\query\QueueBuilder;
use yii\base\ActionEvent;
use yii\base\Controller;
use yii\helpers\ArrayHelper;

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
     * @var array
     */
    private $_actionQueries = [];


    /**
     * @var array|string|null
     */
    private $_fetchedResult;


    /**
     * @var \SplQueue
     */
    private $_queryQueue;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->on(
            self::EVENT_AFTER_ACTION,
            function (ActionEvent $event) {
                if ($this->fetchResult) {
                    $this->_actionQueries = $event->result;
                    $this->fetchQueries();
                    $event->result = $this->_fetchedResult;
                }
                $event->handled = true;
            }
        );
    }


    /**
     * @param null $joinedResponse
     *
     * @throws \ErrorException
     * @throws \ghiyam\apix\exceptions\ClientRequestException
     * @throws \yii\base\InvalidConfigException
     */
    protected function fetchQueries($joinedResponse = null)
    {
        while (!$this->getQueryQueue()->isEmpty()) {
            /** @var Query $query */
            $query = $this->getQueryQueue()->dequeue();
            $query->joinedResponse = $joinedResponse;
            $this->sendQuery($query);
            if ($query->hasJoin()) {
                $joinResult = null;
                while ($query->joinCycleCounter()) {
                    $this->fetchQueries($query->fetched);
                }
            }
            else {
                $this->addResultQuery($query);
            }
        }
    }


    /**
     * @param Query $query
     */
    protected function addResultQuery(Query $query)
    {
        if (empty($this->_fetchedResult)) {
            $this->_fetchedResult = $query->fetched;
        }
        else {
            if (is_array($this->_fetchedResult)) {
                $this->_fetchedResult =
                    ArrayHelper::merge(
                        (array)$this->_fetchedResult,
                        (array)$query->fetched
                    );
            }
            else {
                $this->_fetchedResult .=
                    empty($this->_fetchedResult) ?
                        (string)$query->fetched : " " . (string)$query->fetched;
            }
        }
    }


    /**
     * @return \SplQueue
     * @throws \yii\base\InvalidConfigException
     */
    protected function getQueryQueue()
    {
        if (empty($this->_queryQueue)) {
            $this->_queryQueue = QueueBuilder::build($this->_actionQueries);
        }
        return $this->_queryQueue;
    }


    /**
     * @param Query $query
     *
     * @throws \ErrorException
     * @throws \ghiyam\apix\exceptions\ClientRequestException
     */
    protected function sendQuery(Query &$query)
    {
        $this->module->getServiceWithParams($this->service)->sendRequest($query);
    }

}