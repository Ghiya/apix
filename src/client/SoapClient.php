<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\client;


use ghiyam\apix\exceptions\ClientRequestException;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\UnsetArrayValue;


class SoapClient extends BaseObject
{


    /**
     * @var array
     */
    public $credentials =
        [
            'username' => '',
            'password' => '',
        ];


    /**
     * @var array
     */
    public $params =
        [
            'namespaces' => [],
            'client'     => [],
        ];


    /**
     * @var \SoapClient
     */
    protected $connector;


    /**
     * @var string
     */
    private $_requestOriginal;


    /**
     * @var mixed
     */
    private $_responseOriginal;


    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->credentials['username'])) {
            throw new InvalidConfigException('Property `credentials[\'username\']` must be set.');
        }
        if (empty($this->credentials['password'])) {
            throw new InvalidConfigException('Property `credentials[\'password\']` must be set.');
        }
        if (empty($this->params['namespaces'])) {
            throw new InvalidConfigException('Property `params[\'namespaces\']` must be set.');
        }
        if (empty($this->params['client'])) {
            throw new InvalidConfigException('Property `params[\'client\']` must be set.');
        }
        $this->connector = new \SoapClient(null, $this->params['client']);
    }


    /**
     * @param string $method
     * @param array  $params
     *
     * @return array|mixed|null
     * @throws ClientRequestException
     */
    public function sendRequest($method = "", $params = [])
    {
        $this->_requestOriginal = $this->prepareRequest($method, $params);
        $this->_responseOriginal = $this->connector->__doRequest(
            $this->_requestOriginal,
            $this->params['client']['location'],
            null,
            $this->params['client']['soap_version']
        );
        return $this->prepareResponse();
    }


    /**
     * @param string $method
     * @param array  $params
     *
     * @return string
     */
    protected function prepareRequest($method = "", $params = [])
    {
        // parse non-specific properties
        if (isset($params['showTrace'])) {
            $params = ArrayHelper::merge(
                $params,
                ['showTrace' => new UnsetArrayValue(),]
            );
        }
        // create wrapper
        $request = new \DOMDocument('1.0', 'UTF-8');
        $request->formatOutput = true;
        // envelope
        $envelope = $request->createElement('soapenv:Envelope');
        $envelopeAttribute = $request->createAttribute("xmlns:soapenv");
        $envelopeAttribute->value = $this->params['namespaces']['envelope'];
        $envelope->appendChild($envelopeAttribute);
        // envelope header
        $envelope->appendChild($this->getRequestHeader($request));
        // envelope body
        $envelope->appendChild($this->getRequestBody($request, $method, $params));
        // append whole envelope with context to the wrapper
        $request->appendChild($envelope);
        // return result
        return htmlspecialchars_decode($request->saveXML(), ENT_XML1);
    }


    /**
     * @param \DOMDocument $envelope
     *
     * @return \DOMElement
     */
    protected function getRequestHeader(\DOMDocument $envelope)
    {
        $header = $envelope->createElement('soapenv:Header');
        $headerContext = $envelope->createElement('heads:credentials');
        $headerContextParam = $envelope->createAttribute("xmlns:heads");
        $headerContextParam->value = $this->params['namespaces']['header'];
        $headerContext->appendChild($headerContextParam);
        foreach ($this->credentials as $key => $value) {
            $headerArgEl = $envelope->createElement('heads:' . $key, $value);
            $headerContext->appendChild($headerArgEl);
        }
        $header->appendChild($headerContext);
        return $header;
    }


    /**
     * @param \DOMDocument $envelope
     * @param string       $method
     * @param array        $params
     *
     * @return \DOMElement
     */
    protected function getRequestBody(\DOMDocument $envelope, $method = "", $params = [])
    {
        $body = $envelope->createElement('soapenv:Body');
        $method = $envelope->createElement($method);
        // добавляем элементы body если переданы аргументы запроса
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    $methodParam = $envelope->createElement($key);
                    foreach ($value as $rowValues) {
                        $values = $envelope->createElement('row');
                        foreach ($rowValues as $rowKey => $rowValue) {
                            $values->appendChild($envelope->createElement($rowKey, $rowValue));
                        }
                        $methodParam->appendChild($values);
                    }
                    $method->appendChild($methodParam);
                }
                else {
                    $method->appendChild($envelope->createElement($key, $value));
                }
            }
        }
        $body->appendChild($method);
        return $body;
    }


    /**
     * @return mixed|null
     * @throws ClientRequestException
     */
    protected function prepareResponse()
    {
        if ($this->getResponseXML()->xpath('//return') !== null) {
            $array = Json::decode(Json::encode((array)$this->getResponseXML()->xpath('//return')));
            if (!empty($array[0]['row'])) {
                return $array[0]['row'];
            }
            elseif (isset($array[0][0])) {
                return $array[0][0];
            }
            else {
                return null;
            }
        }
        elseif ($this->getResponseXML()->xpath('soapenvBody')[0] !== null) {
            $response = (array)$this->getResponseXML()->xpath('soapenvBody')[0]->children()->children();
            // если получено сообщение об ошибке
            if (ArrayHelper::isIn('faultcode', array_keys($response))) {
                throw new ClientRequestException(
                    ArrayHelper::getValue($response, 'faultstring') . ": " .
                    ArrayHelper::getValue($response, 'detail')->children()->exceptionName->__toString()
                );
            }
            else {
                return Json::decode(Json::encode((array)$response));
            }
        }
        return null;
    }


    protected function getResponseXML()
    {
        return new \SimpleXMLElement(preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $this->_responseOriginal));
    }

}