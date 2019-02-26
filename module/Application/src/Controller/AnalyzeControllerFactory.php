<?php

namespace Application\Controller;

use Application\Model\CodeAnalyzer\CodeAnalyzer;
use Application\Model\File\RecursiveFileIterator;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class AnalyzeControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $analyzeController = new AnalyzeController();

        // Inject CodeAnalyzer
        $codeAnalyzer = $serviceLocator->get(CodeAnalyzer::class);
        $analyzeController->injectCodeAnalyzer($codeAnalyzer);

        // Inject RecursiveFileIterator
        $fileIterator = new RecursiveFileIterator();
        $analyzeController->injectRecursiveFileIterator($fileIterator);

        return $analyzeController;
    }
}
