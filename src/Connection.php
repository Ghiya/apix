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

/**
 * Class Connection
 * @package Apix
 */
class Connection extends BaseObject
{


    const TYPE_SOAP = 'soap';


    const TYPE_REST = 'rest';


    /**
     * @var string
     */
    public $type = self::TYPE_SOAP;


    /**
     * @var array
     */
    public $clientParams = [];


    /**
     * @var RestClient|SoapClient
     */
    protected $client;


    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->clientParams)) {
            throw new InvalidConfigException("Property `clientParams` must be set.");
        }
        $this->client = $this->type === self::TYPE_SOAP ?
            new SoapClient($this->getClientParams()) : new RestClient($this->getClientParams());
    }


    /**
     * @return array
     */
    protected function getClientParams()
    {
        return
            [
                'credentials' => $this->clientParams['credentials'],
                'params'      => $this->clientParams['params']
            ];
    }


    /**
     * @param Query $query
     *
     * @return array|mixed|null
     * @throws exceptions\ClientRequestException
     */
    final public function send(Query $query)
    {
        $response = $this->client->sendRequest($query->method, $query->params);
        if (!empty($query->result)) {
            // if callable result
            if (is_callable($query->result)) {
                return call_user_func($query->result, $response);
            }
            // if strict value result
            elseif (is_string($query->result) && !is_numeric($query->result)) {
                return
                    is_array($response) && isset($response[$query->result]) ?
                        $response[$query->result] : null;
            }
        }
        return $response;
    }

}