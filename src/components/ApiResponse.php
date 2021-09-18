<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */

namespace ghiyam\apix\components;

/**
 * Class ApiResponse
 *
 * @package ghiyam\apix\components
 */
final class ApiResponse extends \yii\base\BaseObject
{

    /**
     * @var mixed
     */
    public $original;

    /**
     * @var mixed
     */
    public $parsed;
}