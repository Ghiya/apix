<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */

namespace ghiyam\apix\components;

use yii\base\InvalidConfigException;

/**
 * Class ApiRequest
 * @property-read string $method
 * @property-read ?array $params
 *
 * @package ghiyam\apix\components
 */
final class ApiRequest extends \yii\base\BaseObject
{

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var string[]
     */
    private $_allowedTypes = ['GET', 'POST', 'PATCH', 'PUT', 'DELETE'];

    /**
     * @var string
     */
    public $type = 'GET';

    /**
     * @var mixed
     */
    public $original;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->data)) {
            throw new InvalidConfigException("Parameter `data` must be set");
        }
        if (!is_array($this->data)) {
            throw new InvalidConfigException("Parameter `data` must be type of array");
        }
        if (empty($this->data['method'])) {
            throw new InvalidConfigException("Parameter `data['method']` must be set");
        }
        if (!is_string($this->data['method'])) {
            throw new InvalidConfigException("Parameter `data['method']` must be type of string");
        }
        if (!empty($this->data['params']) && !is_array($this->data['params'])) {
            throw new InvalidConfigException("Parameter `data['params']` must be type of array");
        }
        if (!in_array($this->type, $this->_allowedTypes)) {
            throw new InvalidConfigException("Parameter 'type' must be one of " . join(", ", $this->_allowedTypes));
        }
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->data['method'];
    }

    /**
     * @return ?array
     */
    public function getParams(): ?array
    {
        return !empty($this->data['params']) ? $this->data['params'] : null;
    }
}