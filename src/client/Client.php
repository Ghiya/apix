<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\client;


use ghiyam\apix\exceptions\ClientRequestException;
use yii\base\BaseObject;

abstract class Client extends BaseObject
{


    /**
     * @var array
     */
    public $queryParams = [];


    /**
     * @var array
     */
    public $headers = [];


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
     *
     * @return array|mixed|null
     * @throws ClientRequestException
     */
    final public function send($method = "", $params = [])
    {
        $this->_originalRequest = $this->prepareRequest($method, $params);
        $this->_originalResponse = $this->sendRequest($this->_originalRequest);
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
     * @return string
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