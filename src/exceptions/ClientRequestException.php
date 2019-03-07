<?php
/**
 * Copyright (c) 2018-2019. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\exceptions;


use yii\web\HttpException;

/**
 * Class ClientRequestException
 * @package ghiyam\apix\exceptions
 */
class ClientRequestException extends HttpException
{

    /**
     * ClientRequestException constructor.
     * @param null $message
     */
    public function __construct($message = null)
    {
        parent::__construct(400, $message, 0, null);
    }

}