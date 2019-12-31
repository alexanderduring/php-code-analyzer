<?php

declare(strict_types=1);

namespace Application\Model\Project;

class Project
{
    /** @var string */
    private $name;

    /** @var string */
    private $path;

    /** @var array */
    private $ignores;



    public function __construct(string $name, string $path, array $ignores)
    {
        $this->name = $name;
        $this->path = $path;
        $this->ignores = $ignores;
    }



    public function getName(): string
    {
        return $this->name;
    }



    public function getPath(): string
    {
        return $this->path;
    }



    public function getIgnores(): array
    {
        return $this->ignores;
    }
}