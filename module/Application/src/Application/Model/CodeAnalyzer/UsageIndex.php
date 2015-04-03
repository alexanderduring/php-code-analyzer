<?php

namespace Application\Model\CodeAnalyzer;

/**
 * This class holds all usages of
 * classes found in the code.
 */
class UsageIndex
{
    const NOTICE_NEW_WITH_VARIABLE = 'NEW_WITH_VARIABLE';
    const NOTICE_UNKNOWN_NEW = 'UNKNOWN_NEW';

    /** @var array */
    private $index = array(
        'usages' => array(),
        'notices' => array()
    );



    /**
     * @param string $fullyQualifiedName
     * @param string $file
     * @param integer $line
     */
    public function addInstantiation($fullyQualifiedName, $file, $line)
    {
        $this->index['usages'][$fullyQualifiedName]['new'][] = array(
            'file' => $file,
            'line' => $line
        );
    }



    /**
     * @param string $variableName
     * @param string $file
     * @param integer $line
     */
    public function addInstantiationWithVariable($variableName, $file, $line)
    {
        $this->index['notices'][] = array(
            'type' => self::NOTICE_NEW_WITH_VARIABLE,
            'variable' => $variableName,
            'file' => $file,
            'line' => $line
        );
    }



    /**
     * @param string $nodeType
     * @param string $file
     * @param integer $line
     */
    public function addUnknownInstantiation($nodeType, $file, $line)
    {
        $this->index['notices'][] = array(
            'type' => self::NOTICE_UNKNOWN_NEW,
            'nodeType' => $nodeType,
            'file' => $file,
            'line' => $line
        );
    }



    public function __toString()
    {
        $string = "\n";
        $string .= "Found instantiations:\n";
        $string .= "---------------------\n";

        foreach ($this->index['usages'] as $className => $entry) {
            foreach ($entry['new'] as $instantiation) {
                $string .= $className . ": " . $instantiation['file'] . ", line " . $instantiation['line'] . "\n";
            }
        }

        $string .= "\n";
        $string .= "Notices:\n";
        $string .= "--------\n";

        foreach ($this->index['notices'] as $notice) {
            switch ($notice['type']) {
                case self::NOTICE_NEW_WITH_VARIABLE:
                    $string .= "New with variable (" . $notice['variable'] . ") in "  . $notice['file'] . ", line " . $notice['line'] . "\n";
                break;
                case self::NOTICE_UNKNOWN_NEW:
                    $string .= "New with unknown structure (" . $notice['nodeType'] . ") in "  . $notice['file'] . ", line " . $notice['line'] . "\n";
            }

            foreach ($entry['new'] as $instantiation) {
            }
        }

        return $string;
    }
}