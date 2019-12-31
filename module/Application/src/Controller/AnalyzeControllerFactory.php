<?php

namespace Application\Controller;

use Application\Model\CodeAnalyzer\CodeAnalyzer;
use Application\Model\File\FilesProcessor;
use Application\Model\File\RecursiveFileIterator;
use Application\Model\Project\ProjectStorage;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class AnalyzeControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $codeAnalyzer = $serviceLocator->get(CodeAnalyzer::class);
        $filesProcessor = new FilesProcessor();
        $recursiveFileIterator = new RecursiveFileIterator();
        $projectStorage = $serviceLocator->get(ProjectStorage::class);

        return new AnalyzeController(
            $codeAnalyzer,
            $filesProcessor,
            $projectStorage,
            $recursiveFileIterator
        );
    }
}
