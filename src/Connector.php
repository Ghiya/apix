<?php
/**
 * Copyright (c) 2018-2019. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix;


use ghiyam\apix\client\CurlClient;
use ghiyam\apix\client\SmppClient;
use ghiyam\apix\client\SoapClient;
use ghiyam\apix\query\Query;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class Connector
 *
 * @property-read CurlClient|SmppClient|SoapClient
 *
 * @package Apix
 */
class Connector extends BaseObject
{


    const TYPE_SOAP = 'soap';


    const TYPE_REST = 'curl';


    const TYPE_SMPP = 'smpp';

    /**
     * @var string
     */
    public $type = self::TYPE_REST;


    /**
     * @var array
     */
    public $params = [];


    /**
     * @var CurlClient|SoapClient|SmppClient
     */
    private $_client;


    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->params)) {
            throw new InvalidConfigException("Property `params` must be set.");
        }
        switch ($this->type) {

            case self::TYPE_REST :
                $this->_client = new CurlClient($this->params);
                break;

            case self::TYPE_SOAP :
                $this->_client = new SoapClient($this->params);
                break;

            case self::TYPE_SMPP :
                $this->_client = new SmppClient($this->params);
                break;

            default :
                throw new InvalidConfigException("Property `type` has illegal value: $this->type.");
                break;
        }
    }

    /**
     * Возвращает клиента соединения.
     *
     * @return CurlClient|SmppClient|SoapClient
     */
    final public function getClient()
    {
        return $this->_client;
    }

    /**
     * @param Query $query
     *
     * @throws \ErrorException
     * @throws exceptions\ClientRequestException
     */
    final public function sendRequest(Query &$query)
    {
        $query->fetched = $this->_client->send($query->getMethod(), $query->getParams());
        if (!empty($query->result)) {
            $parsedResult = null;
            // if callable result
            if (is_callable($query->result)) {
                $parsedResult = call_user_func($query->result, $query->fetched);
            } // if strict value result
            elseif (is_string($query->result) && !is_numeric($query->result)) {
                $parsedResult = ArrayHelper::getValue($query->fetched, $query->result);
            } // if an array subset result
            elseif (is_array($query->result)) {
                $parsedResult = ArrayHelper::filter($query->fetched, $query->result);
            }
            $query->fetched = $parsedResult;
        }
    }

}