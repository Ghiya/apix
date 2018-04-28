<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\actions;


use ghiyam\apix\controllers\ServiceController;
use ghiyam\apix\query\Query;
use ghiyam\apix\query\QueueBuilder;
use yii\base\Action;
use yii\base\ActionEvent;
use yii\helpers\ArrayHelper;

/**
 * Class FetchAction
 * Действие запроса к API сервиса.
 * Результат выполнения обрабатывается как запрос к API и, соответственно, действие всегда должено возвращать массив
 * параметров запроса.
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
class FetchAction extends Action
{


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
        $this->controller->on(
            ServiceController::EVENT_AFTER_ACTION,
            function (ActionEvent $event) {
                $event->result = $this->fetchResult($event->result);
                $event->handled = true;
            }
        );
    }


    /**
     * @param array $resultQuery
     *
     * @return array|null|string
     * @throws \ErrorException
     * @throws \ghiyam\apix\exceptions\ClientRequestException
     * @throws \yii\base\InvalidConfigException
     */
    protected function fetchResult($resultQuery = [])
    {
        $this->_actionQueries = $resultQuery;
        $this->fetchQueries();
        return $this->_fetchedResult;
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
            $this->controller->sendQuery($query);
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

}