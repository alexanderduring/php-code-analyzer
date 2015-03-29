<?php

namespace Application\Model\CodeAnalyzer;

/**
 * This class holds all usages of
 * classes found in the code.
 */
class UsageIndex
{
    /** @var array */
    private $index = array();



    /**
     * @param string $fullyQualifiedName
     * @param string $file
     * @param integer $line
     */
    public function addInstantiation($fullyQualifiedName, $file, $line)
    {
        $this->index[$fullyQualifiedName]['new'][] = array(
            'file' => $file,
            'line' => $line
        );
    }



    public function __toString()
    {
        $string = '';
        foreach ($this->index as $className => $entry) {
            foreach ($entry['new'] as $instantiation) {
                $string .= $className . ": " . $instantiation['file'] . ", Line " . $instantiation['line'] . "\n";
            }
        }

        return $string;
    }
}