<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */

namespace ghiyam\apix\client;


use ghiyam\apix\exceptions\ServiceUnavailableException;
use ghiyam\apix\exceptions\UnknownAPIException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;

/**
 * Class CurlClient
 *
 * @property resource $connector cURL handler
 *
 * @package ghiyam\apix\client
 */
class CurlClient extends Client
{


    /**
     * @var string
     */
    public $hostPrefix = "http://";


    /**
     * @var string
     */
    public $host;


    /**
     * @var string
     */
    public $port;


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
     * @var bool
     */
    public $checkConnection = true;


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
     *
     * @throws ServiceUnavailableException
     */
    public function sendRequest($originalRequest)
    {
        curl_setopt_array($this->connector, $originalRequest);
        if ($this->checkConnection()) {
            return curl_exec($this->connector);
        } else {
            throw new ServiceUnavailableException($this->getServiceId());
        }
    }


    /**
     * {@inheritdoc}
     *
     * @throws UnknownAPIException
     */
    protected function prepareRequest($method = "", $params = [])
    {
        // устанавливает метод для запроса к API если он указан
        $apiMethod = isset($params['apiMethod']) ? $params['apiMethod'] : '';
        $params = ArrayHelper::merge(
            $params,
            [
                'apiMethod' => new UnsetArrayValue()
            ]
        );
        // switch request type
        switch (strtolower($method)) {

            case 'get':
                $originalRequest =
                    [
                        CURLOPT_URL     => $this->getServerUrl() . "/$apiMethod?" . http_build_query($params),
                        CURLOPT_HTTPGET => true,
                    ];
                break;

            case 'post':
                $originalRequest =
                    [
                        CURLOPT_URL        => $this->getServerUrl() . "/$apiMethod",
                        CURLOPT_POST       => true,
                        CURLOPT_POSTFIELDS => http_build_query($params)
                    ];
                break;

            default :
                throw new UnknownAPIException($this->getServiceId(), $method);
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
        curl_close($this->connector);
        return $originalResponse;
    }


    /**
     * @return bool
     */
    protected function checkConnection()
    {
        if ($this->emulate || !$this->checkConnection) {
            return true;
        }
        $fp = @fsockopen(
            'tcp://' . $this->host,
            80,
            $errCode,
            $errStr,
            1
        );
        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }


    /**
     * @return string
     */
    protected function getServerUrl()
    {
        $serverUrl = $this->hostPrefix . $this->host;
        $serverUrl .= !empty($this->port) ? ":$this->port" : "";
        $serverUrl .= !empty($this->uri) ? "/$this->uri" : "";
        return $serverUrl;
    }

}