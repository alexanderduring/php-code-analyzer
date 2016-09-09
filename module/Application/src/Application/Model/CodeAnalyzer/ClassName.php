<?php

namespace Application\Model\CodeAnalyzer;

class ClassName
{
    private $fullyQualifiedName;



    public function __construct($fullyQualifiedName)
    {
        $this->fullyQualifiedName = ltrim($fullyQualifiedName, '\\');
    }



    public function getNamespace()
    {
        $positionLastBackslash = strrpos($this->fullyQualifiedName, '\\');

        if ($positionLastBackslash !== false) {
            $namespace = '\\';
        } else {
            $namespace = substr($this->fullyQualifiedName, 0, $positionLastBackslash+1);
        }

        return $namespace;
    }
}