<?php
/**
 * Copyright (c) 2018-2019. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\client;


use ghiyam\apix\exceptions\ClientRequestException;
use yii\base\BaseObject;
use yii\helpers\Json;

/**
 * Class Client
 *
 * @property string $cachedResponse
 *
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
     * @var bool
     */
    public $useCache = false;


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
     * @return string
     */
    protected function getServiceId()
    {
        return \Yii::$app->controller->id;
    }


    /**
     * @param string $method
     * @param array  $params
     *
     * @return mixed|null
     * @throws ClientRequestException
     */
    final public function send($method = "", $params = [])
    {
        \Yii::debug("Method: " . $method, __CLASS__);
        \Yii::debug($params, __CLASS__);
        if ($this->emulate) {
            \Yii::debug("Emulated request returns `true`.", __METHOD__);
            return true;
        }
        $this->_originalRequest = $this->prepareRequest($method, $params);
        if ($this->useCache) {
            if (!empty($this->cachedResponse)) {
                \Yii::debug("Cached value");
                $this->_originalResponse = $this->cachedResponse;
            }
            else {
                $this->_originalResponse = $this->sendRequest($this->_originalRequest);
                $this->cachedResponse = $this->_originalResponse;
            }
        }
        else {
            $this->_originalResponse = $this->sendRequest($this->_originalRequest);
        }
        return $this->prepareResponse($this->_originalResponse);
    }


    /**
     * @return string
     */
    public function getCachedResponse()
    {
        $hash = md5(Json::encode($this->_originalRequest));
        return (string)\Yii::$app->cache->get($hash);
    }


    /**
     * @param string $response
     */
    public function setCachedResponse($response)
    {
        $hash = md5(Json::encode($this->_originalRequest));
        \Yii::$app->cache->set($hash, $response);
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