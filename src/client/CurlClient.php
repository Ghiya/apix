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
 * @property-read resource $connector cURL handler
 * @property-read mixed $info
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
    public $jsonEncoded = false;

    /**
     * @var array
     */
    public $curlOptions = [];

    /**
     * @var bool
     */
    public $checkConnection = true;

    /**
     * @var mixed
     */
    private $_info;

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
            $result = curl_exec($this->connector);
            $this->_info = curl_getinfo($this->connector);
            return $result;
        } else {
            throw new ServiceUnavailableException($this->getServiceId());
        }
    }

    /**
     * Возвращает информацию о curl соединении.
     * @return mixed
     */
    public function getInfo()
    {
        return $this->_info;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnknownAPIException
     */
    protected function prepareRequest($method = "", $params = [])
    {
        // для этого клиента хранит конфигурацию curl соединения
        $originalRequest = [];
        // устанавливает метод для запроса к API если он указан
        $apiMethod = isset($params['apiMethod']) ? $params['apiMethod'] : '';
        $params = ArrayHelper::merge(
            $params,
            [
                'apiMethod' => new UnsetArrayValue()
            ]
        );
        if ($this->jsonEncoded) {
            $params = json_encode($params, JSON_UNESCAPED_UNICODE);
        }
        // switch request type
        switch (strtolower($method)) {

            case 'get':
                $originalRequest =
                    [
                        CURLOPT_URL  =>
                            !empty($params) ?
                                $this->getServerUrl() . "/$apiMethod?" . http_build_query($params) :
                                $this->getServerUrl() . "/$apiMethod",
                        CURLOPT_POST => false,
                    ];
                break;

            case 'post':
                $originalRequest =
                    [
                        CURLOPT_URL        => $this->getServerUrl() . "/$apiMethod",
                        CURLOPT_POST       => true,
                        CURLOPT_POSTFIELDS => $params
                    ];
                break;

            default :

                break;
        }
        if (!empty($this->headers)) {
            $originalRequest[CURLOPT_HTTPHEADER] = $this->headers;
        }
        return
            ArrayHelper::merge(
                $originalRequest,
                $this->curlOptions
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