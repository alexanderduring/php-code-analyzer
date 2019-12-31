<?php

declare(strict_types=1);

namespace Application\Model\Project;

class ProjectStorage
{
    private $path = 'data/project';



    public function hasProject(string $projectName): bool
    {
        return is_dir($this->getProjectDir($projectName));
    }



    /**
     * @throws Exception
     */
    public function getProject(string $projectName): Project
    {
        if (!$this->hasProject($projectName)) {
            throw new Exception('Project does not exist.');
        }

        if (!$this->hasProjectFile($projectName)) {
            throw new Exception('Project config file project.json does not exist.');
        }

        $config = $this->loadProjectFile($projectName);
        $path = $config['path'];
        $ignores = array_key_exists('ignores', $config) ? $config['ignores'] : [];

        return new Project($projectName, $path, $ignores);
    }



    /**
     * @throws Exception
     */
    public function getProjects(): Projects
    {
        $projectsDir = $this->getProjectsDir();
        $projectNames = [];

        $directory = opendir($projectsDir);
        if ($directory) {
            while (false !== ($element = readdir($directory))) {
                $elementPath = $projectsDir . '/' . $element;
                if ($element != "." && $element != ".." && is_dir($elementPath)) {
                    $projectNames[] = $element;
                }
            }
            closedir($directory);
        }

        $projects = [];
        foreach ($projectNames as $projectName) {
            $projects[] = $this->getProject($projectName);
        }

        return new Projects($projects);
    }



    /**
     * @throws Exception
     */
    public function createProject(string $projectName, string $projectPath): Project
    {
        $projectDir = $this->getProjectDir($projectName);
        if (mkdir($projectDir) === false) {
            throw new Exception("Could not create project dir '$projectDir'.");
        };

        $projectFile = $this->getProjectFilePath($projectName);
        $project = [
            'path' => $projectPath,
            'ignores' => []
        ];

        file_put_contents($projectFile, json_encode($project, JSON_PRETTY_PRINT));

        return new Project($projectName, $projectPath, []);
    }



    public function deleteProject(string $projectName)
    {
        $projectDir = $this->getProjectDir($projectName);

        $directory = opendir($projectDir);
        if ($directory) {
            while (false !== ($file = readdir($directory))) {
                if ($file != "." && $file != ".." ) {
                    unlink($projectDir. '/' . $file);
                }
            }
            closedir($directory);
            rmdir($projectDir);
        }
    }



    private function getProjectDir(string $projectName): string
    {
        return $this->getProjectsDir() . '/'. $projectName;
    }



    private function getProjectsDir(): string
    {
        return realpath($this->path);
    }



    private function hasProjectFile(string $projectName): bool
    {
        return file_exists($this->getProjectFilePath($projectName));
    }



    private function loadProjectFile(string $projectName): array
    {
        return json_decode(file_get_contents($this->getProjectFilePath($projectName)), true);
    }



    private function getProjectFilePath(string $projectName): string
    {
        return $this->getProjectDir($projectName) . '/project.json';
    }
}