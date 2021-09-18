<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
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
     * @inheritDoc
     */
    public function getName()
    {
        return 'Connection error';
    }

}