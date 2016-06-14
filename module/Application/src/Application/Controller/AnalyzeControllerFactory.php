<?php

namespace Application\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AnalyzeControllerFactory implements FactoryInterface
{
    /** @var \Zend\Mvc\Controller\ControllerManager */
    private $controllerManager;

    /** @var \Zend\ServiceManager\ServiceLocator */
    private $serviceLocator;



    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Application\Controller\AnalyzeController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->controllerManager = $serviceLocator;
        $this->serviceLocator = $serviceLocator->getServiceLocator();

        // Manufacture AnalyzeController
        $analyzeController = new AnalyzeController();

        // Inject CodeAnalyzer
        $codeAnalyzer = $this->serviceLocator->get('CodeAnalyzer');
        $analyzeController->injectCodeAnalyzer($codeAnalyzer);

        return $analyzeController;
    }
}
