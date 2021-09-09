<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */


namespace ghiyam\apix\clients;


use ghiyam\apix\components\ApiClient;
use ghiyam\apix\components\ApiRequest;
use ghiyam\apix\components\ApiResponse;
use SMPP;
use SmppAddress;
use SmppClient;
use SocketTransport;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

/**
 * Class SmppApiClient
 *
 * @property SmppClient $connector;
 *
 * @package ghiyam\apix\clients
 */
abstract class SmppApiClient extends ApiClient
{

    /**
     * @var string
     */
    public $type = ApiClient::TYPE_SMPP;

    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $port;

    /**
     * @var bool
     */
    public $isDebug = false;

    /**
     * @var int
     */
    public $timeout = 3000;

    /**
     * @var int
     */
    public $encoding;

    /**
     * @var SocketTransport
     */
    protected $transport;

    /**
     * @var bool
     */
    private $_isProcessing = false;

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if ($this->isDebug) {
            SocketTransport::$defaultDebug = true;
        }
        $this->transport = new SocketTransport([$this->host], $this->port);
        $this->transport->setRecvTimeout($this->timeout);
        $this->connector = new SmppClient($this->transport);
        // Activate binary hex-output of server interaction
        if ($this->isDebug) {
            $this->connector->debug = true;
        }
    }

    /**
     * @inheritDoc
     */
    protected function beforeFetch(array $requestData, ?string $requestType = "GET"): ApiRequest
    {
        $this->transport->open();
        $this->connector->bindTransmitter($this->credentials['username'], $this->credentials['password']);
        if (!empty($this->clientOptions)) {
            foreach ($this->clientOptions as $option => $value) {
                SmppClient::${$option} = $value;
            }
        }
        return parent::beforeFetch($requestData, $requestType);
    }

    /**
     * @inheritDoc
     */
    protected function afterFetch($response): ApiResponse
    {
        $this->connector->close();
        $this->_isProcessing = false;
        return parent::afterFetch($response);
    }

    /**
     * @inheritDoc
     */
    public function prepareRequest(ApiRequest $apiRequest)
    {
        return [
            'from' => new SmppAddress($apiRequest->method, SMPP::TON_ALPHANUMERIC),
            'to'   => new SmppAddress($apiRequest->params['to'], SMPP::TON_INTERNATIONAL, SMPP::NPI_E164),
            'text' => mb_convert_encoding(trim(strip_tags(Html::decode($apiRequest->params['text']))), "UCS-2BE"),
        ];
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(ApiRequest $apiRequest)
    {
        // @todo исправить багу
        // исправление баги задваивания отправки СМС
        if (!$this->_isProcessing) {
            $this->_isProcessing = true;
            return
                [
                    'id'     =>
                        $this->connector->sendSMS(
                            $apiRequest->original['from'],
                            $apiRequest->original['to'],
                            $apiRequest->original['text'],
                            $this->clientOptions['tags'] ?? null,
                            $this->encoding ?? SMPP::DATA_CODING_DEFAULT
                        ),
                    'source' => $apiRequest->original['to']
                ];
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    abstract public function prepareResponse(ApiResponse $apiResponse);
}