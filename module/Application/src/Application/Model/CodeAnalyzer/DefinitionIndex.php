<?php

namespace Application\Model\CodeAnalyzer;

/**
 * This class holds all definitions of
 * - classes
 * - abstract classes
 * - interfaces
 * found in the code.
 */
class DefinitionIndex
{
    /** @var array */
    private $index = array();



    /**
     * @param string $fullyQualifiedName
     * @param string $type (class|abstract-class|interface)
     */
    public function addClass($fullyQualifiedName, $type)
    {
        $this->index[$fullyQualifiedName] = array(
            'fqn' => $fullyQualifiedName,
            'type' => $type
        );
    }



    /**
     * @param string $fullyQualifiedName
     * @return boolean
     */
    public function hasClass($fullyQualifiedName)
    {
        $hasClass = array_key_exists($fullyQualifiedName, $this->index);
        return $hasClass;
    }



    /**
     * @param string $fullyQualifiedName
     * @return array
     */
    public function getClass($fullyQualifiedName)
    {
        if ($this->hasClass($fullyQualifiedName)) {
            $class = $this->index[$fullyQualifiedName];
        } else {
            $class = array();
        }

        return $class;
    }



    public function __toString()
    {
        $string = '';
        foreach ($this->index as $entry) {
            $string .= $entry['type'] . " ";
            $string .= $entry['fqn'] . "\n";
        }

        return $string;
    }
}