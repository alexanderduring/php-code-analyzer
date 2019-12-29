<?php

declare(strict_types=1);

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ProjectController extends AbstractActionController
{



    public function indexAction()
    {
        echo "This should be the help page.\n\n";

        return;
    }



    public function listAction()
    {
        $projects = [];

        $projectsFile = 'data/projects.json';
        if (file_exists($projectsFile)) {
            $projects = json_decode(file_get_contents($projectsFile), true);
        }

        foreach ($projects as $name => $project) {
            echo $name . ': ' . $project['path'] . "\n";
        }

        return;
    }



    public function newAction()
    {
        $projectPath = (string) $this->getRequest()->getParam('path');
        $projectName = (string) $this->getRequest()->getParam('name', '');

        // Set default name
        if ($projectName == '') {
            $projectName = 'Project-' . time();
        }

        $projectsFile = 'data/projects.json';
        if (file_exists($projectsFile)) {
            $projects = json_decode(file_get_contents($projectsFile), true);
        }

        $projects[$projectName] = [
            'path' => $projectPath
        ];


        file_put_contents($projectsFile, json_encode($projects));
    }
}
