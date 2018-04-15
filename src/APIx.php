<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */

namespace ghiyam\apix;


use ghiyam\apix\controller\ServiceController;
use ghiyam\apix\exceptions\ClientRequestException;
use ghiyam\apix\query\ComposedQuery;
use ghiyam\apix\query\Query;
use ghiyam\apix\query\QueueBuilder;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class APIx
 *
 * @property ServiceController $controller
 *
 * @package ghiyam\apix
 */
class APIx extends Module
{


    /**
     * @var Service[]
     */
    private $_services = [];

    /**
     * {@inheritdoc}
     */
    /*public function bootstrap($app)
    {

    }*/


    /*public function init()
    {
        parent::init();
        \Yii::configure($this, require __DIR__ . '/config/config.php');
    }*/


    /**
     * @param array $serviceParams
     *
     * @return Service
     */
    protected function getServiceWithParams($serviceParams = [])
    {
        $instanceHash = md5(Json::encode($serviceParams));
        if ( !isset($this->_services[$instanceHash]) ) {
            $this->_services[$instanceHash] = new Service($serviceParams);
        }
        return $this->_services[$instanceHash];
    }

    /**
     * @param array $queryParams
     * @param array $serviceParams
     *
     * @return null|array|string
     * @throws ClientRequestException
     * @throws InvalidConfigException
     * @throws \ErrorException
     */
    public function fetch($queryParams = [], $serviceParams = [])
    {
        $queue = QueueBuilder::build($queryParams);
        while (!$queue->isEmpty()) {
            // send query
            //$query = $this->fetchQueue($queue);
            $query = $this->getServiceWithParams($serviceParams)->sendQuery($queue->dequeue());
            // if composed query
            if ($query->isComposed()) {
                // several queries
                if (ArrayHelper::isIndexed($query->join)) {
                    $joinResult = "";
                    foreach ($query->join as $joinedQuery) {
                        $joinResult .= " " .
                            $this
                                ->fetchComposed(
                                    $query->joinIndex,
                                    $joinedQuery,
                                    $query->fetched
                                );
                    }
                    return trim($joinResult);
                }
                // single query
                else {
                    return
                        $this
                            ->fetchComposed(
                                $query->joinIndex,
                                $query->join,
                                $query->fetched
                            );
                }
            }
        }
        return isset($query) ? $query->fetched : null;
    }

    /*protected function hasJoinIndex() {
        return preg_match("/{\w}+/i", implode(":", array_values($joinQuery['params'])));
    }*/

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
    protected function fetchQueue(\SplQueue $queue)
    {
        if (empty($this->connection)) {
            throw new InvalidConfigException("Property `connection` must be set.");
        }
        return $this->getClientWithParams()->sendQuery($queue->dequeue());
    }

}