<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\exceptions;


use yii\base\ExitException;

/**
 * Class ClientRequestException
 * @package ghiyam\apix\exceptions
 */
class ClientRequestException extends ExitException
{


    /**
     * ClientRequestException constructor.
     *
     * @param $message
     */
    public function __construct($message)
    {
        parent::__construct(500, $message, 0);
    }

}