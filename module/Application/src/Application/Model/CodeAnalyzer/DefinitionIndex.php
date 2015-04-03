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

    /** @var string */
    private $filename;



    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }



    /**
     * @param string $fullyQualifiedName
     * @param string $type (class|abstract-class|interface)
     */
    public function addClass($fullyQualifiedName, $type)
    {
        $this->index[$fullyQualifiedName] = array(
            'fqn' => $fullyQualifiedName,
            'type' => $type,
            'file' => $this->filename
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
        $string = "\n";
        $string .= "Found classes:\n";
        $string .= "--------------\n";

        foreach ($this->index as $entry) {
            $string .= $entry['type'] . " ";
            $string .= $entry['fqn'] . ", ";
            $string .= $entry['file'] . "\n";
        }

        return $string;
    }
}