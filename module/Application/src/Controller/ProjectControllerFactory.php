<?php

namespace Application\Controller;

use Application\Model\Project\ProjectStorage;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ProjectControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $projectStorage = $serviceLocator->get(ProjectStorage::class);
        $projectController = new ProjectController($projectStorage);

        return $projectController;
    }
}
