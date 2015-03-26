<?php

namespace Application\Model\CodeAnalyzer;

/**
 * This class holds all definitions of
 * - classes
 * - abstract classes
 * - interfaces
 * found in the code.
 */
class Index
{
    /** @var array */
    private $index = array();



    /**
     * @param string $fullyQualifiedName
     */
    public function addClass($fullyQualifiedName)
    {
        $this->index[$fullyQualifiedName] = $fullyQualifiedName;
    }



    public function __toString()
    {
        $string = '';
        foreach ($this->index as $entry) {
            $string .= $entry . "\n";
        }

        return $string;
    }
}