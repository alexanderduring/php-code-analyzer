<?php

declare(strict_types=1);

namespace Application\Model\Project;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ProjectStorageFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $projectStorage = new ProjectStorage();

        return $projectStorage;
    }
}
