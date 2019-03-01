<?php
/**
 * Copyright (c) 2018. Ghiya <ghiya@mikadze.me>
 */

namespace ghiyam\apix;


use yii\base\BootstrapInterface;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\helpers\Json;

/**
 * Class APIx
 *
 *
 * @package ghiyam\apix
 * @version v0.2.9
 */
class APIx extends Module implements BootstrapInterface
{


    /**
     * @var string
     */
    public $version = "v0.2.9";


    /**
     * @var Connector[]
     */
    private $_connectors = [];


    /**
     * {@inheritdoc}
     *
     * Used to prevent `null` result on [APIx::getInstance()].
     */
    public function bootstrap($app)
    {
    }


    /**
     * @param string $route
     *
     * @return array
     */
    protected function getRouteRules($route = "")
    {
        $routeRules = [];
        foreach ($this->controllerMap as $serviceId => $serviceConfig) {
            if (!empty($serviceConfig['routeRules'][$route])) {
                $routeRules[$serviceId] = $serviceConfig['routeRules'][$route];
            }
        }
        return $routeRules;
    }


    /**
     * @param       $route
     * @param array $params
     *
     * @return string
     * @throws InvalidConfigException
     */
    protected function getServiceRoute($route, $params = [])
    {
        if (!preg_match("/\//i", $route)) {
            foreach ($this->getRouteRules($route) as $serviceId => $routeRules) {
                $model = new DynamicModel($params);
                foreach ($routeRules as $routeRule) {
                    $model->addRule($routeRule[0], $routeRule[1], isset($routeRule[2]) ? $routeRule[2] : []);
                }
                $model->validate();
                if (!$model->hasErrors()) {
                    return "$serviceId/$route";
                }
            }
            throw new InvalidConfigException("API service was not found for the route `$route`.");
        }
        return $route;
    }


    /**
     * @param string $route
     * @param array  $params
     *
     * @return mixed
     * @throws InvalidConfigException
     * @throws \yii\base\InvalidRouteException
     */
    public function runAction($route, $params = [])
    {
        return parent::runAction($this->getServiceRoute($route, $params), $params);
    }


    /**
     * @param array $params
     *
     * @return Connector
     */
    public function getConnector($params = [])
    {
        $instanceHash = md5(Json::encode($params));
        if (!isset($this->_connectors[$instanceHash])) {
            $this->_connectors[$instanceHash] = new Connector($params);
        }
        return $this->_connectors[$instanceHash];
    }

}