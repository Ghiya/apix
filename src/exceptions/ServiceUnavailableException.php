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
     * @param                 $serviceControllerId
     */
    public function __construct($serviceControllerId)
    {
        parent::__construct(500, $this->getExceptionMessage($serviceControllerId), 0, null);
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Connection error';
    }


    /**
     * @param $serviceControllerId
     * @param $apiActionId
     *
     * @return string
     */
    protected function getExceptionMessage($serviceControllerId)
    {
        return defined("YII_DEBUG") && YII_DEBUG ?
            "Unavailable API service `$serviceControllerId`." :
            "Requested service is unavailable.";
    }

}