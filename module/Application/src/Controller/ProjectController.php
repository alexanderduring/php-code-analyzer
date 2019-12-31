<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\Project\Exception as ProjectException;
use Application\Model\Project\ProjectStorage;
use Exception;
use Zend\Mvc\Controller\AbstractActionController;

class ProjectController extends AbstractActionController
{
    /** @var ProjectStorage */
    private $projectStorage;


    public function __construct(ProjectStorage $projectStorage)
    {
        $this->projectStorage = $projectStorage;
    }



    public function indexAction()
    {
        echo "This should be the help page.\n\n";

        return;
    }



    public function listAction()
    {
        $projects = $this->projectStorage->getProjects();
        foreach ($projects as $project) {
            echo $project->getName() . ': ' . $project->getPath() . "\n";
        }

        return;
    }



    public function addAction()
    {
        $parameterPath = (string) $this->getRequest()->getParam('path');
        $projectName = (string) $this->getRequest()->getParam('name');

        try {
            if (!preg_match('/[A-z0-9-_]+/', $projectName)) {
                throw new Exception('Name is not well formed. Allowed characters: A-Za-z0-9-_');
            }

            if ($this->projectStorage->hasProject($projectName)) {
                throw new Exception("Project name '$projectName' already exists.");
            }

            $projectPath = realpath($parameterPath);
            if ($projectPath === false) {
                throw new Exception('Path does not exist.');
            }

            $project = $this->projectStorage->createProject($projectName, $projectPath);

            echo "Created project '{$project->getName()}' with path: {$project->getPath()}.\n\n";

        } catch (ProjectException $projectException) {
            echo $projectException->getMessage() . "\n\n";
        } catch (Exception $exception) {
            echo $exception->getMessage() . "\n\n";
        }
    }



    public function removeAction()
    {
        $projectName = (string) $this->getRequest()->getParam('name');

        try {
            if (!preg_match('/[A-z0-9-_]+/', $projectName)) {
                throw new Exception('Name is not well formed. Allowed characters: A-Za-z0-9-_');
            }

            $this->projectStorage->deleteProject($projectName);
            echo "Deleted project '$projectName'.\n\n";

        } catch (Exception $exception) {
            echo $exception->getMessage() . "\n\n";
        }
    }
}
