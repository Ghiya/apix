<?php
/*
 * @copyright Copyright (c) 2018-2021
 * @author Ghiya Mikadze <g.mikadze@lakka.io>
 */

namespace ghiyam\apix;


use Yii;
use yii\base\BootstrapInterface;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\base\Module;
use yii\console\Exception;
use yii\console\Response;
use yii\helpers\Json;

/**
 * Class APIx
 *
 *
 * @package ghiyam\apix
 * @version 2.0.0
 */
class APIx extends Module implements BootstrapInterface
{


    /**
     * @var string
     */
    public $version = "2.0.0";


    /**
     * @var Service[]
     */
    private $_services = [];


    /**
     * @inheritDoc
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
    protected function getRouteRules(string $route = ""): array
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
    protected function getServiceRoute($route, array $params = []): string
    {
        if (!preg_match("/\//i", $route)) {
            foreach ($this->getRouteRules($route) as $serviceId => $routeRules) {
                $model = new DynamicModel($params);
                foreach ($routeRules as $routeRule) {
                    $model->addRule($routeRule[0], $routeRule[1], $routeRule[2] ?? []);
                }
                $model->validate();
                if (!$model->hasErrors()) {
                    return "$serviceId/$route";
                }
            }
            throw new InvalidConfigException("API service was not found for the route `$route`");
        }
        return $route;
    }


    /**
     * @param string $route
     * @param array  $params
     * @return int|mixed|Response
     * @throws InvalidConfigException
     * @throws InvalidRouteException
     * @throws Exception
     */
    public function runAction($route, $params = [])
    {
        return Yii::$app->runAction("/$this->id/" . $this->getServiceRoute($route, $params), $params);
    }


    /**
     * @param array $params
     *
     * @return Service
     */
    public function getService(array $params = []): Service
    {
        $instanceHash = md5(Json::encode($params));
        if (!isset($this->_services[$instanceHash])) {
            $this->_services[$instanceHash] = new Service($params);
        }
        return $this->_services[$instanceHash];
    }

}