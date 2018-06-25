<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\query;


use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\UnsetArrayValue;


class Query extends BaseObject
{


    const REG_JOIN_PARAM = "\|\w+\|";


    const REG_JOIN_RESPONSE = "\|\*\|";


    /**
     * @var string
     */
    public $method = "";


    /**
     * @var array
     */
    public $join = [];


    /**
     * @var string|array
     */
    public $joinedResponse;


    /**
     * @var null|string|callable
     */
    public $result;


    /**
     * @var string|array|bool|null
     */
    public $fetched;


    /**
     * @var mixed|string
     */
    private $_method = "";


    /**
     * @var array
     */
    private $_params = [];


    /**
     * @var int|null
     */
    private $_joinCyclesCounter;


    /**
     * @var string
     */
    private $_hash = "";


    /**
     * Query constructor.
     *
     * @param array $config
     *
     * @throws InvalidConfigException
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['method'])) {
            throw new InvalidConfigException("Parameter `method` must be set in the configuration array.");
        }
        $this->_method = $config['method'];
        $config = ArrayHelper::merge(
            $config,
            [
                'method' => new UnsetArrayValue()
            ]
        );
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
     * @return mixed|string
     * @throws \ErrorException
     */
    public function getMethod()
    {
        $this->_applyJoin();
        return $this->_method;
    }


    /**
     * @return array
     * @throws \ErrorException
     */
    public function getParams()
    {
        $this->_applyJoin();
        return $this->_params;
    }


    /**
     * @throws \ErrorException
     */
    private function _applyJoin()
    {
        if ($this->hasJoinParams()) {
            if (empty($this->joinedResponse)) {
                throw new \ErrorException("Cannot join request with `null` response.");
            }
            if ($this->_joinParam()) {
                $queryString = $this->__toString();
                preg_match_all("/" . self::REG_JOIN_PARAM . "/i", $queryString, $joinIndexes, PREG_SET_ORDER);
                if (!empty($joinIndexes)) {
                    foreach ($joinIndexes as $joinIndex) {
                        $joinIndexParam = substr($joinIndex[0], 1, strlen($joinIndex[0]) - 2);
                        if (!isset($this->joinedResponse[$joinIndexParam])) {
                            throw new \ErrorException("Join index param `$joinIndexParam` was not found in response.");
                        }
                        else {
                            $queryString = preg_replace("/\|" . $joinIndexParam . "\|/i",
                                $this->joinedResponse[$joinIndexParam], $queryString);
                        }
                    }
                }
                $this->__fromString($queryString);
            }
            elseif ($this->_joinResponse()) {
                $this->__fromString(preg_replace("/" . self::REG_JOIN_RESPONSE . "/i", $this->joinedResponse,
                    $this->__toString()));
            }
        }
    }


    /**
     * @return int
     */
    public function hasJoin()
    {
        return !empty($this->join);
    }


    /**
     * @return bool
     */
    protected function hasJoinParams()
    {
        return $this->_joinParam() || $this->_joinResponse();
    }


    /**
     * @return false|int
     */
    private function _joinParam()
    {
        return preg_match("/" . self::REG_JOIN_PARAM . "/i", $this->__toString());
    }


    /**
     * @return false|int
     */
    private function _joinResponse()
    {
        return preg_match("/" . self::REG_JOIN_RESPONSE . "/i", $this->__toString());
    }


    /**
     * @param bool $reset
     *
     * @return int|null
     */
    public function joinCycleCounter($reset = false)
    {
        if (!isset($this->_joinCyclesCounter) || $reset) {
            $this->_joinCyclesCounter = count($this->join);
        }
        else {
            $this->_joinCyclesCounter--;
        }
        return $this->_joinCyclesCounter;
    }


    /**
     * @param $queryString
     */
    public function __fromString($queryString)
    {
        $this->_method = strstr($queryString, "&&", true);
        $this->_params = Json::decode(substr(strstr($queryString, "&&"), 2));
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->_method . "&&" . Json::encode($this->_params);
    }


}