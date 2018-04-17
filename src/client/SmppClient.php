<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\client;


use yii\base\InvalidConfigException;
use yii\helpers\Html;

/**
 * Class SmppClient
 *
 * @property \SmppClient $connector;
 *
 * @package ghiyam\apix\client
 */
class SmppClient extends RestClient
{


    /**
     * @var bool
     */
    public $isDebug = false;

    /**
     * @var array
     */
    public $smpp = [];


    /**
     * @var \SocketTransport
     */
    protected $transport;



    public function init()
    {
        parent::init();
        if (empty($this->smpp)) {
            throw new InvalidConfigException('Property `smpp` must be set.');
        }
        if (empty($this->smpp['username'])) {
            throw new InvalidConfigException('Property `smpp[\'username\']` must be set.');
        }
        if (empty($this->smpp['password'])) {
            throw new InvalidConfigException('Property `smpp[\'password\']` must be set.');
        }

        $this->transport = new \SocketTransport([$this->host], $this->port);
        $this->transport->setRecvTimeout(10000);
        $this->connector = new \SmppClient($this->transport);
        // Activate binary hex-output of server interaction
        if ( $this->isDebug ) {
            $this->connector->debug = true;
            $this->transport->debug = true;
        }
        if (empty($this->smpp['options'])) {
            foreach($this->smpp['options'] as $option => $value) {
                \SmppClient::${$option} = $value;
            }
        }
        /*\SmppClient::$sms_null_terminate_octetstrings = false;
        \SmppClient::$csms_method = \SmppClient::CSMS_PAYLOAD;
        \SmppClient::$sms_registered_delivery_flag = \SMPP::REG_DELIVERY_SMSC_BOTH;*/
    }

    protected function prepareRequest($method = "", $params = [], $clientParams = [])
    {
        $this->transport->open();
        $this->connector->bindTransmitter($this->smpp['username'], $this->smpp['password']);
        return [
            'from' => new \SmppAddress($method, \SMPP::TON_ALPHANUMERIC),
            'to'   => new \SmppAddress($params['to'], \SMPP::TON_INTERNATIONAL, \SMPP::NPI_E164),
            'text' => mb_convert_encoding(trim(strip_tags(Html::decode($params['text']))), "UCS-2BE"),
        ];
    }

    protected function prepareResponse($originalResponse)
    {
        var_dump($originalResponse);
        die;
    }

    public function sendRequest($originalRequest)
    {
        $messageId = $this->connector->sendSMS(
            $originalRequest['from'],
            $originalRequest['to'],
            $originalRequest['text'],
            isset($this->smpp['tags']) ? $this->smpp['tags'] : null,
            isset($this->smpp['encoding']) ? $this->smpp['encoding'] : \SMPP::DATA_CODING_DEFAULT
        );
        return $this->connector->queryStatus($messageId, $originalRequest['to']);
    }

}