<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\query;


use yii\base\BaseObject;
use yii\helpers\ArrayHelper;


class Query extends BaseObject
{


    /**
     * @var string
     */
    public $method = "";


    /**
     * @var array
     */
    public $params = [];


    /**
     * @var null|string|callable
     */
    public $result;


    /**
     * @param array $response
     *
     * @return $this
     */
    public function buildOn($response = [])
    {
        if (!empty($response) && is_array($response)) {
            foreach ($this->params as $key => $value) {
                // replace with value from the result
                if (preg_match("/{\w+}/i", $value)) {
                    $replacedParam = substr($value, 1, strlen($value) - 2);
                    $replacedValue = ArrayHelper::getValue($response, $replacedParam);
                    if (!empty($replacedValue)) {
                        $this->params[$key] = str_replace($value, $replacedValue, $this->params[$key]);
                    }
                }
                // replace with the plain result
                elseif (preg_match("/\*/i", $value)) {
                    $this->params[$key] = $response;
                }
            }
        }
        return $this;
    }

}