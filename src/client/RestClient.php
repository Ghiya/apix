<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */

namespace ghiyam\apix\client;


use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class RestClient
 *
 * @property resource $connector cURL handler
 *
 * @package ghiyam\apix\client
 */
class RestClient extends Client
{


    const REQUEST_METHOD_GET = 'GET';


    const REQUEST_METHOD_POST = 'POST';


    const REQUEST_METHOD_HEAD = 'HEAD';


    const REQUEST_METHOD_PATCH = 'PATCH';


    const REQUEST_METHOD_UPDATE = 'UPDATE';


    const REQUEST_METHOD_DELETE = 'DELETE';


    /**
     * @var string
     */
    public $host = "localhost";


    /**
     * @var string
     */
    public $port = "80";


    /**
     * @var string
     */
    public $uri = "";


    /**
     * @var bool
     */
    public $secured = false;


    /**
     * @var int
     */
    public $timeout = 3;


    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->host)) {
            throw new InvalidConfigException('Property `host` must be set.');
        }
        $this->connector = curl_init();
        curl_setopt_array(
            $this->connector,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => $this->timeout
            ]
        );
    }


    /**
     * {@inheritdoc}
     */
    public function sendRequest($originalRequest)
    {
        curl_setopt_array($this->connector, $originalRequest);
        return curl_exec($this->connector);
    }


    /**
     * {@inheritdoc}
     */
    protected function prepareRequest($method = "", $params = [], $clientParams = [])
    {
        // set request type
        switch ($this->requestType($clientParams)) {

            case 'GET':
                $originalRequest =
                    [
                        CURLOPT_URL     => $this->getServerUrl() . "/$method?" . http_build_query($params),
                        CURLOPT_HTTPGET => true,
                    ];
                break;

            case 'POST':
                $originalRequest =
                    [
                        CURLOPT_URL        => $this->getServerUrl() . "/$method",
                        CURLOPT_POST       => true,
                        CURLOPT_POSTFIELDS => http_build_query($params)
                    ];
                break;

            default :
                $originalRequest =
                    [
                        CURLOPT_URL           => $this->getServerUrl() . "/$method",
                        CURLOPT_CUSTOMREQUEST => $this->requestType($clientParams)
                    ];
                break;
        }
        return
            ArrayHelper::merge(
                $originalRequest,
                [
                    CURLOPT_HTTPHEADER =>
                        $this->headers
                ]
            );
    }


    /**
     * {@inheritdoc}
     */
    protected function prepareResponse($originalResponse)
    {
        return Json::decode($originalResponse);
    }


    /**
     * @return string
     */
    protected function getServerUrl()
    {
        $serverUrl = "";
        $serverUrl .= $this->secured ? "https://$this->host" : "http://$this->host";
        $serverUrl .= !empty($this->port) ? ":$this->port" : "";
        $serverUrl .= !empty($this->uri) ? "$this->uri" : "";
        return $serverUrl;
    }


    /**
     * @param array $clientParams
     *
     * @return mixed|string
     */
    protected function requestType($clientParams = [])
    {
        return
            isset($clientParams['requestMethod']) ? $clientParams['requestMethod'] : self::REQUEST_METHOD_GET;
    }


}