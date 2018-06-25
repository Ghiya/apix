<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\exceptions;


use yii\web\HttpException;

/**
 * Class ServiceUnavailableException
 * Исключение выбрасываемое при отсутствии соединения с сервисом API.
 *
 * @package ghiyam\apix\exceptions
 */
class ServiceUnavailableException extends HttpException
{


    /**
     * ServiceUnavailableException constructor.
     *
     * @param                 $serviceId
     */
    public function __construct($serviceId)
    {
        parent::__construct(500, "Unavailable to connect to service `" . $serviceId . "`.", 0, null);
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Connection error';
    }

}