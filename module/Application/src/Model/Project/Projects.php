<?php

declare(strict_types=1);

namespace Application\Model\Project;

class Projects implements \Iterator
{
    /** @var Project[]|array */
    private $array = [];

    /** @var int */
    private $position = 0;



    /**
     * @throws Exception
     */
    public function __construct(array $projects)
    {
        foreach ($projects as $project) {
            if (!$project instanceof Project) {
                throw new Exception('Array should only contain instances of Application\Model\Project\Project');
            }
            $this->array[] = $project;
        }
    }



    public function rewind() {
        $this->position = 0;
    }



    public function current() {
        return $this->array[$this->position];
    }



    public function key() {
        return $this->position;
    }



    public function next() {
        ++$this->position;
    }



    public function valid() {
        return isset($this->array[$this->position]);
    }
}