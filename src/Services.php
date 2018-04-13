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
     * @return mixed|null|string
     * @throws ClientRequestException
     * @throws InvalidConfigException
     * @throws \ErrorException
     */
    public function fetch($query = [])
    {
        $queue = QueueBuilder::build($query);
        $fetchResult = null;
        while (!$queue->isEmpty()) {
            /** @var ComposedQuery|Query $query */
            $query = $queue->dequeue();
            // send query
            $fetchResult = $this->sendQuery($query);
            // composed query
            if ($query instanceof ComposedQuery) {
                // several queries
                if (ArrayHelper::isIndexed($query->join)) {
                    $joinResult = "";
                    foreach ($query->join as $joinedQuery) {
                        $joinResult .= " " .
                            $this
                                ->fetchComposed(
                                    $query->joinIndex,
                                    $joinedQuery,
                                    $fetchResult
                                );
                    }
                    return $joinResult;
                }
                // single query
                else {
                    return
                        $this
                            ->fetchComposed(
                                $query->joinIndex,
                                $query->join,
                                $fetchResult
                            );
                }
            }
        }
        return $fetchResult;
    }


    /**
     * @param array $joinIndex
     * @param array $joinQuery
     * @param       $fetchResult
     *
     * @return mixed|null|string
     * @throws ClientRequestException
     * @throws InvalidConfigException
     * @throws \ErrorException
     */
    protected function fetchComposed($joinIndex = [], $joinQuery = [], $fetchResult)
    {
        if (isset($fetchResult)) {
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
                    $joinParams =
                        [
                            'params' =>
                                [
                                    $joinIndex[0] => $fetchResult[$joinIndex[1]]
                                ]
                        ];
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
     * @param $query
     *
     * @return mixed|null
     * @throws ClientRequestException
     * @throws InvalidConfigException
     */
    protected function sendQuery($query)
    {
        if (empty($this->connection)) {
            throw new InvalidConfigException("Property `connection` must be set.");
        }
        return $this->connection->send($query);
    }

}