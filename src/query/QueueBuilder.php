<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */

namespace ghiyam\apix\query;


use yii\helpers\ArrayHelper;

class QueueBuilder
{


    /**
     * @var \SplQueue
     */
    protected static $queue;


    /**
     * @param array $queryParams
     *
     * @return \SplQueue
     * @throws \yii\base\InvalidConfigException
     */
    public static function build($queryParams = [])
    {
        self::$queue = new \SplQueue();
        self::$queue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE);
        self::buildQueue($queryParams);
        return self::$queue;
    }


    /**
     * @param array $queryParams
     *
     * @throws \yii\base\InvalidConfigException
     */
    protected static function buildQueue($queryParams = [])
    {
        if (empty($queryParams['join'])) {
            self::addQuery($queryParams);
        }
        else {
            self::addQuery($queryParams);
            self::join($queryParams['join']);
        }
    }


    /**
     * @param array $queryParams
     *
     * @throws \yii\base\InvalidConfigException
     */
    protected static function addQuery($queryParams = [])
    {
        self::$queue->enqueue(new Query($queryParams));
    }


    /**
     * @param array $joinParams
     *
     * @throws \yii\base\InvalidConfigException
     */
    protected static function join($joinParams = [])
    {
        if (ArrayHelper::isIndexed($joinParams)) {
            foreach ($joinParams as $queryParams) {
                self::buildQueue($queryParams);
            }
        }
        else {
            self::addQuery($joinParams);
        }
    }

}