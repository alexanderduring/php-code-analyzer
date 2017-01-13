<?php

namespace Application\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MenuFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        $serviceLocator = $helperPluginManager->getServiceLocator();

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