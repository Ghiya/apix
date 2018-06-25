<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */

namespace ghiyam\apix\client;


use ghiyam\apix\exceptions\UnknownAPIException;
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


    /**
     * @var string
     */
    public $host = "localhost";


    /**
     * @var string
     */
    public $port = "";


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
     * @var array
     */
    protected $allowedMethods =
        ['get', 'post', 'head', 'patch', 'update', 'delete', 'put'];


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
     *
     * @throws UnknownAPIException
     */
    protected function prepareRequest($method = "", $params = [])
    {
        $method = strtolower($method);
        if (!in_array($method, $this->allowedMethods)) {
            throw new UnknownAPIException(\Yii::$app->controller->id, 'method');
        }
        // set request type
        switch ($method) {

            case 'get':
                $originalRequest =
                    [
                        CURLOPT_URL     => $this->getServerUrl() . "/$method?" . http_build_query($params),
                        CURLOPT_HTTPGET => true,
                    ];
                break;

            case 'post':
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
                        CURLOPT_CUSTOMREQUEST => $method
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

}