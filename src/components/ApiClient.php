<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */


namespace ghiyam\apix\components;


use Exception;
use ghiyam\apix\exceptions\ApiClientFetchException;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Class ApiClient
 * @property-read string $serviceId
 *
 * @package ghiyam\apix\components
 */
abstract class ApiClient extends BaseObject
{

    const TYPE_SOAP = 'soap';


    const TYPE_CURL = 'curl';


    const TYPE_SMPP = 'smpp';

    /**
     * @var bool
     */
    public $emulate = false;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $credentials = [];

    /**
     * @var array
     */
    public $clientOptions = [];

    /**
     * @var array
     */
    private $_allowedTypes = [
        self::TYPE_CURL,
        self::TYPE_SMPP,
        self::TYPE_SOAP
    ];

    /**
     * @var resource
     */
    protected $connector;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (!in_array($this->type, $this->_allowedTypes)) {
            throw new InvalidConfigException("Property `type` must be one of" . join(", ", $this->_allowedTypes));
        }
    }

    /**
     * @param array       $requestData
     * @param string|null $requestType
     * @return ApiRequest
     */
    protected function beforeFetch(array $requestData, ?string $requestType = "GET"): ApiRequest
    {
        $apiRequest = new ApiRequest(['data' => $requestData, 'type' => $requestType]);
        $apiRequest->original = $this->prepareRequest($apiRequest);
        return $apiRequest;
    }

    /**
     * @param $response
     * @return ApiResponse
     * @throws ApiClientFetchException
     */
    protected function afterFetch($response): ApiResponse
    {
        if (empty($response)) {
            throw new ApiClientFetchException("Server response is `NULL`.");
        }
        $apiResponse = new ApiResponse(['original' => $response]);
        $apiResponse->parsed = $this->prepareResponse($apiResponse);
        return $apiResponse;
    }

    /**
     * @param array       $requestData
     * @param string|null $requestType
     * @return bool|mixed
     * @throws ApiClientFetchException
     */
    final public function fetch(array $requestData, ?string $requestType = "GET")
    {
        try {
            $apiRequest = $this->beforeFetch($requestData, $requestType);
            Yii::debug("Requesting service: " . $this->getServiceId(), __CLASS__);
            Yii::debug("Method: " . $apiRequest->method, __CLASS__);
            Yii::debug($apiRequest->params, __CLASS__);
            if ($this->emulate) {
                Yii::debug("Emulated request returns `true`.", __METHOD__);
                return true;
            }
            $response = $this->sendRequest($apiRequest);
            $apiResponse = $this->afterFetch($response);
            return $apiResponse->parsed;
        } catch (Exception $exception) {
            throw new ApiClientFetchException($exception->getMessage());
        }
    }

    /**
     * @return string
     */
    final public function getServiceId(): string
    {
        return Yii::$app->controller->id;
    }

    /**
     * @param ApiRequest $apiRequest
     * @return mixed
     */
    abstract function prepareRequest(ApiRequest $apiRequest);

    /**
     * @param ApiRequest $apiRequest
     * @return mixed
     */
    abstract function sendRequest(ApiRequest $apiRequest);

    /**
     * @param ApiResponse $apiResponse
     * @return mixed
     */
    abstract function prepareResponse(ApiResponse $apiResponse);

}