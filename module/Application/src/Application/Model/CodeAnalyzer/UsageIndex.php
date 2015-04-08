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
     * @param integer $line
     */
    public function addInstantiation($fullyQualifiedName, $context, $line)
    {
        $this->index['usages'][$fullyQualifiedName]['new'][] = array(
            'context' => $context,
            'file' => $this->filename,
            'line' => $line
        );
    }



    /**
     * @param string $variableName
     * @param integer $line
     */
    public function addInstantiationWithVariable($variableName, $context, $line)
    {
        $this->index['notices'][] = array(
            'type' => self::NOTICE_NEW_WITH_VARIABLE,
            'variable' => $variableName,
            'context' => $context,
            'file' => $this->filename,
            'line' => $line
        );
    }



    /**
     * @param string $nodeType
     * @param integer $line
     */
    public function addUnknownInstantiation($nodeType, $context, $line)
    {
        $this->index['notices'][] = array(
            'type' => self::NOTICE_UNKNOWN_NEW,
            'nodeType' => $nodeType,
            'context' => $context,
            'file' => $this->filename,
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
                $string .= "New " . $className;
                $string .= " in " . $instantiation['context'];
                $string .= " (" . $instantiation['file'] . ", line " . $instantiation['line'] . ")\n";
            }
        }

        $string .= "\n";
        $string .= "Notices:\n";
        $string .= "--------\n";

        foreach ($this->index['notices'] as $notice) {

            switch ($notice['type']) {
                case self::NOTICE_NEW_WITH_VARIABLE:
                    $string .= "New with variable (new " . $notice['variable'] . ")";
                    break;
                case self::NOTICE_UNKNOWN_NEW:
                    $string .= "New with unknown structure (" . $notice['nodeType'] . ")";
                    break;
            }

            $string .= " in " . $notice['context'];
            $string .= " (" . $notice['file'] . ", line " . $notice['line'] . ")\n";
        }

        return $string;
    }
}
