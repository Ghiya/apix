<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\query;


use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\UnsetArrayValue;


class Query extends BaseObject
{


    const TYPE_SIMPLE = 0;


    const TYPE_COMPOSED = 1;


    /**
     * @var string
     */
    public $method = "";


    /**
     * @var array
     */
    public $client = [];


    /**
     * @var array
     */
    public $join = [];


    /**
     * @var string|array
     */
    public $joinResult;


    /**
     * @var string
     */
    public $joinIndex = [];


    /**
     * @var null|string|callable
     */
    public $result;


    /**
     * @var string|array|bool|null
     */
    public $fetched;


    /**
     * @var string|array
     */
    public $lastResponse;

    /**
     * @var array
     */
    private $_params = [];


    /**
     * @var string
     */
    private $_toString = "";


    /**
     * @var string
     */
    private $_hash = "";


    /**
     * Query constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // move `params` from `config` to private variable
        if (isset($config['params'])) {
            $this->_params = $config['params'];
            $config = ArrayHelper::merge(
                $config,
                [
                    'params' => new UnsetArrayValue()
                ]
            );
        }
        parent::__construct($config);
    }


    /**
     * @return int
     */
    public function getType()
    {
        return
            !empty($this->join) && !empty($this->joinIndex) ?
                self::TYPE_COMPOSED : self::TYPE_SIMPLE;
    }


    /**
     * @return bool
     */
    public function isComposed()
    {
        return $this->getType() == self::TYPE_COMPOSED;
    }


    /**
     * @return bool
     */
    public function isSimple()
    {
        return $this->getType() == self::TYPE_SIMPLE;
    }


    public function hasJoinedIndex()
    {
        return preg_match("/\[:\w+:\]/i", $this->__toString());
    }

    public function hasJoinedResponse()
    {
        return preg_match("/\[:\*:\]/i", $this->__toString());
    }

    private function _joinParams()
    {
        if ( $this->hasJoinedIndex() ) {
            //$joinIndex = strstr(strstr($this->_toString(), "[:"), ":]", true);
            preg_match_all("/\[:\w+:\]/i", $this->__toString(), $joinIndexes, PREG_SET_ORDER );
            if ( !empty($joinIndexes)) {
                $joinedHash = $this->__toString();
                foreach ($joinIndexes as $joinIndex) {
                    $responseJoinIndex = substr($joinIndex[0], 2, strlen($joinIndex[0]) - 4);
                    var_dump($this->fetched);die;
                    if ( isset($this->fetched[$responseJoinIndex]) ) {
                        $joinedHash = preg_replace("/\[:".$joinIndex[0].":\]/i", $this->fetched[$joinIndex[[0]]], $joinedHash);
                    }
                    else {
                        throw new \ErrorException("Join index `$responseJoinIndex` value was not found in response.");
                    }
                }
                var_dump($joinedHash);die;
            }
            // if substitution found
            if ( isset($this->fetched[$joinIndex]) ) {
                $joinedHash = preg_replace("/\[:\w+:\]/i", $this->fetched[$joinIndex], $this->__toString());
                return
                    Json::decode(strstr($joinedHash, "&&"));
            }
        }
        elseif( $this->hasJoinedResponse() ) {

        }
        return [];
    }

    /**
     * @return array
     */
    public function getParams()
    {
        /*if ( $this->hasJoinIndex() ) {
            echo $this->_toString();
            die;
        }*/
        return
            ArrayHelper::merge(
                $this->_params,
                $this->_joinParams()
            );
    }


    /**
     * @return string
     */
    public function getHash()
    {
        if (empty($this->_hash)) {
            $this->_hash = md5($this->__toString());
        }
        return $this->_hash;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        if (empty($this->_toString)) {
            $this->_toString = (string)$this->method . "&&" . Json::encode($this->_params);
        }
        return $this->_toString;
    }


}