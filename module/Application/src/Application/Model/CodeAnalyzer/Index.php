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
    const USAGE_TYPE_DECLARATION = 'type-declaration';

    const NOTICE_NEW_WITH_VARIABLE = 'new-with-variable';
    const NOTICE_UNKNOWN_NEW = 'unknown-new';
    const NOTICE_UNKNOWN_USE = 'unknown-use';

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
     * @param array $nameParts
     * @param string $type (class|abstract-class|interface)
     * @param array $extendedClass
     * @param array $implementedInterfaces
     * @param integer $startLine
     * @param integer $endLine
     */
    public function addClass($nameParts, $type, $extendedClass, $implementedInterfaces, $startLine, $endLine)
    {
        $fullyQualifiedName = implode('\\', $nameParts);

        // Add class definition to index
        $this->index['definitions'][$fullyQualifiedName] = array(
            'name' => array(
                'fqn' => $fullyQualifiedName,
                'parts' => $nameParts
            ),
            'type' => $type,
            'extends' => $extendedClass,
            'implements' => $implementedInterfaces,
            'file' => $this->filename,
            'startLine' => $startLine,
            'endLine' => $endLine
        );

        $this->updateNamespaceStatistik($nameParts);
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
     * @param integer $startLine
     * @param integer $endLine
     */
    public function addInstantiation($fullyQualifiedName, $context, $startLine, $endLine)
    {
        $this->addUsage(self::USAGE_NEW, $fullyQualifiedName, $context, $this->filename, $startLine, $endLine);
    }



    /**
     * @param string $variableName
     * @param string $context
     * @param integer $startLine
     * @param integer $endLine
     */
    public function addInstantiationWithVariable($variableName, $context, $startLine, $endLine)
    {
        $notice = array(
            'type' => self::NOTICE_NEW_WITH_VARIABLE,
            'variable' => $variableName
        );

        $this->addNotice($notice, $context, $startLine, $endLine);
    }



    /**
     * @param string $nodeType
     * @param string $context
     * @param integer $startLine
     * @param integer $endLine
     */
    public function addUnknownInstantiation($nodeType, $context, $startLine, $endLine)
    {
        $notice = array(
            'type' => self::NOTICE_UNKNOWN_NEW,
            'nodeType' => $nodeType
        );

        $this->addNotice($notice, $context, $startLine, $endLine);
    }



    /**
     * @param string $fullyQualifiedName
     * @param string $context
     * @param integer $startLine
     * @param integer $endLine
     */
    public function addUseStatement($fullyQualifiedName, $context, $startLine, $endLine)
    {
        $this->addUsage(self::USAGE_USE, $fullyQualifiedName, $context, $this->filename, $startLine, $endLine);
    }



    /**
     * @param string $nodeType
     * @param string $context
     * @param integer $startLine
     * @param integer $endLine
     */
    public function addUnknownUseStatement($nodeType, $context, $startLine, $endLine)
    {
        $notice = array(
            'type' => self::NOTICE_UNKNOWN_USE,
            'nodeType' => $nodeType
        );

        $this->addNotice($notice, $context, $startLine, $endLine);
    }



    public function addTypeDeclaration($fullyQualifiedName, $context, $startLine, $endLine)
    {
        $this->addUsage(self::USAGE_TYPE_DECLARATION, $fullyQualifiedName, $context, $this->filename, $startLine, $endLine);
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
    public function getNamespaces()
    {
        return $this->index['namespaces'];
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
     * @param integer $startLine
     * @param integer $endLine
     */
    private function addUsage($typeOfUsage, $fullyQualifiedName, $context, $filename, $startLine, $endLine)
    {
        $this->index['usages'][$fullyQualifiedName][$typeOfUsage][] = array(
            'context' => $context,
            'file' => $filename,
            'startLine' => $startLine,
            'endLine' => $endLine
        );
    }



    /**
     * @param array $notice
     * @param array $context
     * @param integer $startLine
     * @param integer $endLine
     */
    private function addNotice($notice, $context, $startLine, $endLine)
    {
        $notice['context'] = $context;
        $notice['file'] = $this->filename;
        $notice['startLine'] = $startLine;
        $notice['endLine'] = $endLine;

        $this->index['notices'][] = $notice;
    }



    private function updateNamespaceStatistik($nameParts)
    {
        // Remove class name
        array_pop($nameParts);

        // find out current namespace and increase direct descendents
        $namespace = empty($nameParts) ? '\\' : implode('\\', $nameParts);
        $this->increaseDirectCountForNamespace($namespace);

        // increase count for all parent namespaces
        while (count($nameParts) > 0) {
            array_pop($nameParts);
            $subNamespace = $namespace;
            $namespace = empty($nameParts) ? '\\' : implode('\\', $nameParts);
            $this->updateParentNamespace($namespace, $subNamespace);
        }
    }



    private function increaseDirectCountForNamespace($namespace)
    {
        $this->createNamespaceEntryIfNotExists($namespace);
        $this->index['namespaces'][$namespace]['directDescendents'] += 1;
    }



    private function updateParentNamespace($namespace, $subNamespace)
    {
        $this->createNamespaceEntryIfNotExists($namespace);
        $this->index['namespaces'][$namespace]['allDescendents'] += 1;

        // Here I use the key to store the array entries, because it is much faster than using unique afterwards
        $this->index['namespaces'][$namespace]['subNamespaces'][$subNamespace] = true;
    }



    private function createNamespaceEntryIfNotExists($namespace)
    {
        if (!array_key_exists($namespace, $this->index['namespaces'])) {
            $this->index['namespaces'][$namespace] = array(
                'name' => array(
                    'fqn' => $namespace
                ),
                'directDescendents' => 0,
                'allDescendents' => 0,
                'subNamespaces' => array()
            );
        }
    }
}
