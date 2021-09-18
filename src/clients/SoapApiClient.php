<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */


namespace ghiyam\apix\clients;

use ghiyam\apix\components\ApiClient;
use ghiyam\apix\components\ApiRequest;
use ghiyam\apix\components\ApiResponse;
use SoapClient;
use SoapFault;
use yii\base\InvalidConfigException;


/**
 * Class SoapApiClient
 *
 * @property SoapClient $connector SOAP clients
 *
 * @package ghiyam\apix\clients
 */
abstract class SoapApiClient extends ApiClient
{

    /**
     * @var string
     */
    public $type = ApiClient::TYPE_SOAP;


    /**
     * @var array
     */
    public $namespaces =
        [
            'header'   => '',
            'envelope' => ''
        ];

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigException
     * @throws SoapFault
     */
    public function init()
    {
        parent::init();
        if (empty($this->namespaces)) {
            throw new InvalidConfigException("Property `namespaces` must be set");
        }
        if (empty($this->clientOptions['location'])) {
            throw new InvalidConfigException("Property `clientOptions['location']` must be set");
        }
        if (empty($this->clientOptions['uri'])) {
            throw new InvalidConfigException("Property `clientOptions['uri']` must be set");
        }
        // устанавливает параметры контекста если они определены
        if (!empty($this->clientOptions['stream_context'])) {
            $this->clientOptions['stream_context'] = stream_context_create($this->clientOptions['stream_context']);
        }
        $this->connector = new SoapClient(null, $this->clientOptions);
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(ApiRequest $apiRequest)
    {
        return
            $this->connector->__doRequest(
                $apiRequest->original,
                $this->clientOptions['location'],
                null,
                $this->clientOptions['soap_version']
            );
    }

    /**
     * @inheritDoc
     */
    abstract function prepareRequest(ApiRequest $apiRequest);

    /**
     * @inheritDoc
     */
    abstract function prepareResponse(ApiResponse $apiResponse);
}