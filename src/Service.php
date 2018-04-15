<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix;


use ghiyam\apix\client\RestClient;
use ghiyam\apix\client\SoapClient;
use ghiyam\apix\query\Query;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class Service
 * @package Apix
 */
class Service extends BaseObject
{


    const TYPE_SOAP = 'soap';


    const TYPE_REST = 'rest';


    /**
     * @var string
     */
    public $type = self::TYPE_REST;


    /**
     * @var array
     */
    public $params = [];


    /**
     * @var RestClient|SoapClient
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
        $this->_client = $this->type === self::TYPE_SOAP ?
            new SoapClient($this->params) : new RestClient($this->params);
    }


    /**
     * @param Query $query
     *
     * @return Query|mixed|null
     * @throws exceptions\ClientRequestException
     */
    final public function sendQuery(Query $query)
    {
        $this->_client->queryParams =
            ArrayHelper::merge(
                $this->_client->queryParams,
                !empty($query->client) ? $query->client : []
            );
        $query->fetched = $this->_client->send($query->method, $query->getParams());
        var_dump($query->fetched);
        if (!empty($query->result)) {
            // if callable result
            if (is_callable($query->result)) {
                $query->fetched = call_user_func($query->result, $query->fetched);
            }
            // if strict value result
            elseif (is_string($query->result) && !is_numeric($query->result)) {
                $query->fetched =
                    is_array($query->fetched) && isset($response[$query->result]) ?
                        $query->fetched[$query->result] : null;
            }
        }
        return $query;
    }

}