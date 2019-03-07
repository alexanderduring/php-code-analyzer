<?php

declare(strict_types=1);

namespace Application\Model\CodeAnalyzer\Context;

class Context
{
    /** @var string */
    private $fileName = '';

    /** @var array */
    private $classes = ['global'];



    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }



    public function getFileName(): string
    {
        return $this->fileName;
    }



    public function enterClass(array $fqnParts)
    {
        $fullyQualifiedClassName = implode('\\', $fqnParts);
        array_unshift($this->classes, $fullyQualifiedClassName);
    }



    public function leaveClass()
    {
        array_shift($this->classes);
    }



    public function getClass(): string
    {
        return $this->classes[0];
    }
}
