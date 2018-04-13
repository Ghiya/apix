<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */

namespace ghiyam\apix\query;


class QueueBuilder
{


    /**
     * @var \SplQueue
     */
    protected static $queue;


    /**
     * @param array $query
     *
     * @return \SplQueue
     */
    public static function build($query = [])
    {
        self::$queue = new \SplQueue();
        self::$queue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE);
        if (empty($query['join'])) {
            self::buildSingle($query);
        }
        else {
            self::buildComposed($query);
        }
        return self::$queue;
    }


    /**
     * @param array $query
     */
    protected static function buildSingle($query = [])
    {
        self::$queue->enqueue(new Query($query));
    }


    /**
     * @param array $queries
     */
    protected static function buildComposed($queries = [])
    {
        self::$queue->enqueue(new ComposedQuery($queries));
    }

}