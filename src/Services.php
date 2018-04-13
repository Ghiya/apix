<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */

namespace ghiyam\apix;


use ghiyam\apix\exceptions\ClientRequestException;
use ghiyam\apix\query\ComposedQuery;
use ghiyam\apix\query\Query;
use ghiyam\apix\query\QueueBuilder;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;


class Services extends BaseObject
{


    /**
     * @var Connection
     */
    public $connection;


    /**
     * @param array $query
     *
     * @return null|array|string
     * @throws ClientRequestException
     * @throws InvalidConfigException
     * @throws \ErrorException
     */
    public function fetch($query = [])
    {
        $queue = QueueBuilder::build($query);
        while (!$queue->isEmpty()) {
            // send query
            $fetchedQuery = $this->fetchQueryQueue($queue);
            // if composed query
            if ($fetchedQuery->isComposed()) {
                // several queries
                if (ArrayHelper::isIndexed($fetchedQuery->join)) {
                    $joinResult = "";
                    foreach ($fetchedQuery->join as $joinedQuery) {
                        $joinResult .= " " .
                            $this
                                ->fetchComposed(
                                    $fetchedQuery->joinIndex,
                                    $joinedQuery,
                                    $fetchedQuery->fetched
                                );
                    }
                    return trim($joinResult);
                }
                // single query
                else {
                    return
                        $this
                            ->fetchComposed(
                                $fetchedQuery->joinIndex,
                                $fetchedQuery->join,
                                $fetchedQuery->fetched
                            );
                }
            }
        }
        return isset($fetchedQuery) ? $fetchedQuery->fetched : null;
    }


    /**
     * @param array $joinIndex
     * @param array $joinQuery
     * @param array $fetchResult
     *
     * @return null|array|string
     * @throws ClientRequestException
     * @throws InvalidConfigException
     * @throws \ErrorException
     */
    protected function fetchComposed($joinIndex = [], $joinQuery = [], $fetchResult = [])
    {
        if (isset($fetchResult)) {
            $joinParams = [];
            if (preg_match("/\*/i", $joinIndex[1])) {
                $joinParams =
                    [
                        'params' =>
                            [
                                $joinIndex[0] => $fetchResult
                            ]
                    ];
            }
            else {
                if (isset($fetchResult[$joinIndex[1]])) {
                    // search in method
                    if ( preg_match("/{\w}+/i", $joinQuery['method'])) {

                    }
                    // search in params
                    /*elseif( preg_match("/{\w}+/i", implode(":", array_values($joinQuery['params']))) ) {

                    }*/
                    // join by default
                    else {
                        $joinParams =
                            [
                                'params' =>
                                    [
                                        $joinIndex[0] => $fetchResult[$joinIndex[1]]
                                    ]
                            ];
                    }
                }
                else {
                    throw new \ErrorException("Improper `joinIndex` value.");
                }
            }
            return
                $this->fetch(
                    ArrayHelper::merge(
                        $joinParams,
                        $joinQuery
                    )
                );
        }
        throw new \ErrorException("Cannot join request with `null` response.");
    }


    /**
     * @param \SplQueue $queue
     *
     * @return ComposedQuery|Query
     * @throws ClientRequestException
     * @throws InvalidConfigException
     */
    protected function fetchQueryQueue(\SplQueue $queue)
    {
        if (empty($this->connection)) {
            throw new InvalidConfigException("Property `connection` must be set.");
        }
        return $this->connection->sendQuery($queue->dequeue());
    }

}