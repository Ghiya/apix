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
 * @version v0.2.1
 */
class APIx extends Module implements BootstrapInterface
{


    /**
     * @var string
     */
    public $version = "v0.2.1";


    /**
     * @var Service[]
     */
    private $_services = [];


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
     * @return int|string
     * @throws InvalidConfigException
     */
    protected function getDefaultService()
    {
        foreach ($this->controllerMap as $serviceId => $serviceConfig) {
            if (!empty($serviceConfig['isDefault'])) {
                return $serviceId;
            }
        }
        throw new InvalidConfigException("The requested service API was not found and default service is not defined.");
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
            return $this->getDefaultService() . "/$route";
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
     * @param array $serviceParams
     *
     * @return Service
     */
    public function getServiceWithParams($serviceParams = [])
    {
        $instanceHash = md5(Json::encode($serviceParams));
        if (!isset($this->_services[$instanceHash])) {
            $this->_services[$instanceHash] = new Service($serviceParams);
        }
        return $this->_services[$instanceHash];
    }

}