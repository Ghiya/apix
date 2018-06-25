<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\client;


use ghiyam\apix\exceptions\ClientRequestException;
use yii\base\BaseObject;

/**
 * Class Client
 * @package ghiyam\apix\client
 */
abstract class Client extends BaseObject
{


    /**
     * @var array
     */
    public $headers = [];


    /**
     * @var bool
     */
    public $emulate = false;


    /**
     * @var object
     */
    protected $connector;


    /**
     * @var string
     */
    protected $_originalRequest;


    /**
     * @var mixed
     */
    protected $_originalResponse;


    /**
     * @param string $method
     * @param array  $params
     * @param array  $clientParams
     *
     * @return mixed|null
     * @throws ClientRequestException
     */
    final public function send($method = "", $params = [])
    {
        \Yii::debug($method, __CLASS__);
        \Yii::debug($params, __CLASS__);
        $this->_originalRequest = $this->prepareRequest($method, $params);
        \Yii::debug($this->_originalRequest, __CLASS__);
        $this->_originalResponse = $this->sendRequest($this->_originalRequest);
        \Yii::debug($this->_originalResponse, __CLASS__);
        return $this->prepareResponse($this->_originalResponse);
    }


    /**
     * @param string|array $originalRequest
     *
     * @return array|mixed|null
     * @throws ClientRequestException
     */
    abstract public function sendRequest($originalRequest);


    /**
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     */
    abstract protected function prepareRequest($method = "", $params = []);


    /**
     * @param mixed|null $originalResponse
     *
     * @return mixed|null
     * @throws ClientRequestException
     */
    abstract protected function prepareResponse($originalResponse);

}