<?php

namespace Application\View\Helper;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class MenuFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $menu = new Menu();

        // Inject RouteMatch
        $routeMatch = $serviceLocator->get('Application')->getMvcEvent()->getRouteMatch();
        $menu->injectRouteMatch($routeMatch);

        // Inject Config
        $config = $serviceLocator->get('Config');
        $menuConfig = $config['menu'];
        $menu->injectConfig($menuConfig);

        return $menu;
    }
}
