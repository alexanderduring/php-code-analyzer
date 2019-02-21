<?php

namespace Application\View\Helper;

use Zend\Router\RouteMatch;
use Zend\View\Helper\AbstractHelper as AbstractHelper;

class Menu extends AbstractHelper
{
    const SEPARATOR_ROUTE = '-----';

    /** @var RouteMatch */
    private $routeMatch;

    /** @var array */
    private $config;



    public function injectRouteMatch(RouteMatch $routeMatch)
    {
        $this->routeMatch = $routeMatch;
    }



    /**
     * @param array $config
     */
    public function injectConfig(array $config)
    {
        $this->config = $config;
    }



    /**
     * @return \Application\View\Helper\Menu
     */
    public function __invoke()
    {
        return $this;
    }



    /**
     * @return string
     */
    public function __toString()
    {
        $variables = array(
            'config' => $this->config,
            'matchedRoute' => $this->routeMatch->getMatchedRouteName()
        );
        try {
            $menu = $this->view->render('application/viewhelper/menu', $variables);
        } catch (\Exception $e) {
            var_dump($e);
        }

        return $menu;
    }
}
