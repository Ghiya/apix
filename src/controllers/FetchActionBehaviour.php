<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */

namespace ghiyam\apix\controllers;


use ghiyam\apix\actions\FetchAction;
use ghiyam\apix\exceptions\ApiClientFetchException;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\base\Controller;

class FetchActionBehaviour extends Behavior
{


    /**
     * @var ServiceController
     */
    public $owner;


    /**
     * {@inheritDoc}
     */
    public function events()
    {
        return
            [
                Controller::EVENT_AFTER_ACTION => function (ActionEvent $actionEvent) {
                    $this->fetchAction($actionEvent);
                }
            ];
    }

    /**
     * @param ActionEvent $actionEvent
     * @throws ApiClientFetchException
     */
    public function fetchAction(ActionEvent $actionEvent)
    {
        if ($actionEvent->action instanceof FetchAction) {
            $actionEvent->result = $this->_runFetchAction($actionEvent->action, $actionEvent->result);
            $actionEvent->handled = $actionEvent->action->eventHandled;
        }
    }

    /**
     * @param FetchAction $fetchAction
     * @param array       $actionResult
     * @return array|bool|mixed
     * @throws ApiClientFetchException
     */
    private function _runFetchAction(FetchAction $fetchAction, array $actionResult)
    {
        return $this->owner->getApiService()->send($actionResult, $fetchAction->requestType);
    }
}