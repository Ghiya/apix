<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */


namespace ghiyam\apix;


use ghiyam\apix\components\ApiClient;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class Service
 *
 * @package ghiyam\apix
 */
class Service extends BaseObject
{

    /**
     * @var array
     */
    public $client;

    /**
     * @var ApiClient
     */
    private $_apiClient;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->client)) {
            throw new InvalidConfigException("Property `client` must be set");
        }
        $this->_apiClient = \Yii::createObject($this->client);
    }

    /**
     *
     * @throws exceptions\ApiClientFetchException
     */
    final public function send(array $requestData, ?string $requestType)
    {
        $result = $this->_apiClient->fetch($requestData, $requestType);
        if (!empty($apiAction->parsedResult)) {
            // if callable result
            if (is_callable($apiAction->parsedResult)) {
                $result = call_user_func($apiAction->parsedResult, $result);
            } // if strict value result
            elseif (is_string($apiAction->parsedResult) && !is_numeric($apiAction->parsedResult)) {
                $result = ArrayHelper::getValue($result, $apiAction->parsedResult);
            } // if an array subset result
            elseif (is_array($apiAction->parsedResult)) {
                $result = ArrayHelper::filter($result, $apiAction->parsedResult);
            }
        }
        return $result;
    }

}