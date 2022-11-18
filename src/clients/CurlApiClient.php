<?php
/*
 * @copyright Copyright (c) 2018-2022
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */

namespace ghiyam\apix\clients;


use ghiyam\apix\components\ApiClient;
use ghiyam\apix\components\ApiRequest;
use ghiyam\apix\components\ApiResponse;
use ghiyam\apix\exceptions\ServiceUnavailableException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class CurlApiClient
 *
 * @property-read resource $connector cURL handler
 * @property-read array    $info      cURL request info
 * @property-read string   $error     cURL request error
 *
 * @package ghiyam\apix\clients
 */
abstract class CurlApiClient extends ApiClient
{

    /**
     * @var string
     */
    public $type = ApiClient::TYPE_CURL;

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
     * @var int
     */
    public $timeout = 3;

    /**
     * @var array
     */
    public $headers = [];
    /**
     * @var bool
     */
    public $jsonEncodedRequest = false;

    /**
     * @var bool
     */
    public $checkConnection = true;

    /**
     * @var mixed
     */
    private $_info;

    /**
     * @var mixed
     */
    private $_error;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->host)) {
            throw new InvalidConfigException('Property `host` must be set.');
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
     * Возвращает информацию об ошибке curl соединения.
     * @return mixed
     */
    public function getError()
    {
        return $this->_error;
    }

    protected function beforeFetch(array $requestData, ?string $requestType = "GET"): ApiRequest
    {
        $this->connector = curl_init();
        curl_setopt_array(
            $this->connector,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => $this->timeout
            ]
        );
        return parent::beforeFetch($requestData, $requestType);
    }

    protected function afterFetch($response): ApiResponse
    {
        curl_close($this->connector);
        return parent::afterFetch($response);
    }

    /**
     * @inheritDoc
     */
    public function prepareRequest(ApiRequest $apiRequest)
    {
        // switch request type
        switch ($apiRequest->type) {

            case 'POST':
            case 'PUT':
            case 'PATCH':
                $params = $this->jsonEncodedRequest ? json_encode($apiRequest->params,
                    JSON_UNESCAPED_UNICODE) : $apiRequest->params;
                $curlOptions =
                    [
                        CURLOPT_URL           => $this->getServerUrl() . "/$apiRequest->method",
                        CURLOPT_CUSTOMREQUEST => $apiRequest->type,
                        CURLOPT_POSTFIELDS    => $params
                    ];
                break;

            case 'DELETE':
                $curlOptions =
                    [
                        CURLOPT_URL           => $this->getServerUrl() . "/$apiRequest->method",
                        CURLOPT_CUSTOMREQUEST => $apiRequest->type,

                    ];
                break;

            // GET
            default :
                $curlOptions =
                    [
                        CURLOPT_URL           =>
                            !empty($apiRequest->params) ?
                                $this->getServerUrl() . "/$apiRequest->method?" . http_build_query($apiRequest->params) :
                                $this->getServerUrl() . "/$apiRequest->method",
                        // CURLOPT_POST => false,
                        CURLOPT_CUSTOMREQUEST => $apiRequest->type,
                    ];
                break;
        }
        if (!empty($this->headers)) {
            $curlOptions[CURLOPT_HTTPHEADER] = $this->headers;
        }
        return
            ArrayHelper::merge(
                $curlOptions,
                $this->clientOptions
            );
    }

    /**
     * @inheritDoc
     *
     * @throws ServiceUnavailableException
     */
    public function sendRequest(ApiRequest $apiRequest)
    {
        curl_setopt_array($this->connector, $apiRequest->original);
        $result = curl_exec($this->connector);
        $this->_info = curl_getinfo($this->connector);
        $this->_error = curl_error($this->connector);
        return $result;
    }

    /**
     * @inheritDoc
     */
    abstract function prepareResponse(ApiResponse $apiResponse);


    /**
     * @return string
     */
    protected function getServerUrl(): string
    {
        $serverUrl = !empty($this->port) ? "$this->host:$this->port" : $this->host;
        $serverUrl .= !empty($this->uri) ? "/$this->uri" : "";
        return $serverUrl;
    }

    /**
     * @return bool
     * @todo имплементировать корректно или удалить в дальнейшем
     */
    private function _checkConnection()
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
}