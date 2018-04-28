<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */


namespace ghiyam\apix\exceptions;


use yii\web\HttpException;

/**
 * Class UnknownAPIException
 * Исключение выбрасываемое при попытке вызова неизвестного действия контроллера сервиса API.
 *
 * @package ghiyam\apix\exceptions
 */
class UnknownAPIException extends HttpException
{


    /**
     * UnknownAPIException constructor.
     *
     * @param                 $serviceControllerId
     * @param                 $apiActionId
     * @param \Exception|null $previous
     */
    public function __construct($serviceControllerId, $apiActionId, \Exception $previous = null)
    {
        parent::__construct(400, $this->getExceptionMessage($serviceControllerId, $apiActionId), 0, $previous);
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return defined("YII_DEBUG") && YII_DEBUG ? 'Unknown API action' : parent::getName();
    }


    /**
     * @param $serviceControllerId
     * @param $apiActionId
     *
     * @return string
     */
    protected function getExceptionMessage($serviceControllerId, $apiActionId)
    {
        return defined("YII_DEBUG") && YII_DEBUG ?
            "Unknown `$serviceControllerId` service API action `$apiActionId`." :
            "Internal Server Error";
    }

}