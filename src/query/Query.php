<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\query;


use yii\base\BaseObject;


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
    public $params = [];


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

}