<?php

namespace Application\Model\CodeAnalyzer;

/**
 * This class holds all results of the code analyzing
 *
 * 1) Definitions of
 * - classes
 * - abstract classes
 * - interfaces
 * found in the code.
 *
 * 2) Usages classes like
 * - new
 * found in the code.
 *
 */
class Index
{
    const USAGE_NEW = 'new';
    const USAGE_USE = 'use';

    const NOTICE_NEW_WITH_VARIABLE = 'NEW_WITH_VARIABLE';
    const NOTICE_UNKNOWN_NEW = 'UNKNOWN_NEW';
    const NOTICE_UNKNOWN_USE = 'UNKNOWN_USE';

    /** @var array */
    private $index = array(
        'definitions' => array(),
        'namespaces' => array(),
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
     * @param string $type (class|abstract-class|interface)
     * @param integer $startLine
     * @param integer $endLine
     */
    public function addClass($fullyQualifiedName, $type, $startLine, $endLine)
    {
        $namespaceName = $this->getNamespaceFromFqn($fullyQualifiedName);

        // Add class definition to index
        $this->index['definitions'][$fullyQualifiedName] = array(
            'fqn' => $fullyQualifiedName,
            'namespace' => $namespaceName,
            'type' => $type,
            'file' => $this->filename,
            'startLine' => $startLine,
            'endLine' => $endLine
        );

        // Add/update namespace information
        if (!array_key_exists($namespaceName, $this->index['namespaces'])) {
            $this->index['namespaces'][$namespaceName] = array(
                'directDescendents' => 0,
                'allDescendents' => null,
                'subNamespaces' => array()
            );
        }
        $this->index['namespaces'][$namespaceName]['directDescendents'] += 1;
        $this->index['namespaces'][$namespaceName]['directDescendents'] += 1;
        $namespaces = $this->index['namespaces'];
    }



    /**
     * @param string $fullyQualifiedName
     * @return boolean
     */
    public function hasClass($fullyQualifiedName)
    {
        $hasClass = array_key_exists($fullyQualifiedName, $this->index['definitions']);

        return $hasClass;
    }



    /**
     * @param string $fullyQualifiedName
     * @return array
     */
    public function getClass($fullyQualifiedName)
    {
        if ($this->hasClass($fullyQualifiedName)) {
            $class = $this->index['definitions'][$fullyQualifiedName];
        } else {
            $class = array();
        }

        return $class;
    }



    /**
     * @param string $fullyQualifiedName
     * @param string $context
     * @param integer $line
     */
    public function addInstantiation($fullyQualifiedName, $context, $line)
    {
        $this->addUsage(self::USAGE_NEW, $fullyQualifiedName, $context, $this->filename, $line);
    }



    /**
     * @param string $variableName
     * @param string $context
     * @param integer $line
     */
    public function addInstantiationWithVariable($variableName, $context, $line)
    {
        $notice = array(
            'type' => self::NOTICE_NEW_WITH_VARIABLE,
            'variable' => $variableName
        );

        $this->addNotice($notice, $context, $line);
    }



    /**
     * @param string $nodeType
     * @param string $context
     * @param integer $line
     */
    public function addUnknownInstantiation($nodeType, $context, $line)
    {
        $notice = array(
            'type' => self::NOTICE_UNKNOWN_NEW,
            'nodeType' => $nodeType
        );

        $this->addNotice($notice, $context, $line);
    }



    /**
     * @param string $fullyQualifiedName
     * @param string $context
     * @param integer $line
     */
    public function addUseStatement($fullyQualifiedName, $context, $line)
    {
        $this->addUsage(self::USAGE_USE, $fullyQualifiedName, $context, $this->filename, $line);
    }



    /**
     * @param string $nodeType
     * @param string $context
     * @param integer $line
     */
    public function addUnknownUseStatement($nodeType, $context, $line)
    {
        $notice = array(
            'type' => self::NOTICE_UNKNOWN_USE,
            'nodeType' => $nodeType
        );

        $this->addNotice($notice, $context, $line);
    }



    /**
     * @return array
     */
    public function getDefinitions()
    {
        return $this->index['definitions'];
    }



    /**
     * @return array
     */
    public function getUsages()
    {
        return $this->index['usages'];
    }



    /**
     * @return array
     */
    public function getNotices()
    {
        return $this->index['notices'];
    }



    /**
     * @param string $typeOfUsage
     * @param string $fullyQualifiedName
     * @param string $context
     * @param string $filename
     * @param integer $line
     */
    private function addUsage($typeOfUsage, $fullyQualifiedName, $context, $filename, $line)
    {
        $this->index['usages'][$fullyQualifiedName][$typeOfUsage][] = array(
            'context' => $context,
            'file' => $filename,
            'line' => $line
        );
    }


    /**
     * @param array $notice
     * @param array $context
     * @param integer $line
     */
    private function addNotice($notice, $context, $line)
    {
        $notice['context'] = $context;
        $notice['file'] = $this->filename;
        $notice['line'] = $line;

        $this->index['notices'][] = $notice;
    }



    /**
     * @param string $fullyQualifiedName
     * @return string
     */
    private function getNamespaceFromFqn($fullyQualifiedName)
    {
        $fullyQualifiedName = ltrim($fullyQualifiedName, '\\');
        $positionLastBackslash = strrpos($fullyQualifiedName, '\\');

        if ($positionLastBackslash !== false) {
            $namespace = '\\';
        } else {
            $namespace = substr($fullyQualifiedName, 0, $positionLastBackslash+1);
        }

        return $namespace;
    }
}
