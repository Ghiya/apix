<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\exceptions;


use yii\base\ExitException;

class ClientRequestException extends ExitException
{


    public function __construct($message)
    {
        parent::__construct(500, $message, 0);
    }

}