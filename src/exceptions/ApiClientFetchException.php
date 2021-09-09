<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */


namespace ghiyam\apix\exceptions;


use yii\web\HttpException;

/**
 * Class ApiClientFetchException
 *
 * @package ghiyam\apix\exceptions
 */
class ApiClientFetchException extends HttpException
{

    /**
     * ApiClientFetchException constructor.
     * @param null $message
     */
    public function __construct($message = null, $code = 0)
    {
        parent::__construct(400, $message, $code, null);
    }

}