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
    const USAGE_CONST_FETCH = 'const-fetch';
    const USAGE_NEW = 'new';
    const USAGE_STATIC_CALL = 'static-call';
    const USAGE_TYPE_DECLARATION = 'type-declaration';
    const USAGE_USE = 'use';

    const NOTICE_CONST_FETCH_WITH_VARIABLE = 'const-fetch-with-variable';
    const NOTICE_NEW_WITH_VARIABLE = 'new-with-variable';
    const NOTICE_STATC_CALL_WITH_VARIABLE = 'static-call-with-variable';
    const NOTICE_UNKNOWN_NEW = 'unknown-new';
    const NOTICE_UNKNOWN_USE = 'unknown-use';

    /** @var array */
    private $index = array(
        'definitions' => array(),
        'namespaces' => array(),
        'usages' => array(),
        'notices' => array()
    );

    public $foundNodeTypes = array();



    public function addNodeType(string $type)
    {
        if (!in_array($type, $this->foundNodeTypes)) {
            $this->foundNodeTypes[] = $type;
            //echo $type."\n";
        }
    }



    public function addClass(array $nameParts, string $type, array $extendedClass, array $implementedInterfaces, string $filename, int $startLine, int $endLine)
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
            'file' => $filename,
            'startLine' => $startLine,
            'endLine' => $endLine
        );

        $this->updateNamespaceStatistik($nameParts, $endLine - $startLine);
    }



    public function hasClass(string $fullyQualifiedName): bool
    {
        $hasClass = array_key_exists($fullyQualifiedName, $this->index['definitions']);

        return $hasClass;
    }



    public function getClass(string $fullyQualifiedName): array
    {
        if ($this->hasClass($fullyQualifiedName)) {
            $class = $this->index['definitions'][$fullyQualifiedName];
        } else {
            $class = array();
        }

        return $class;
    }



    public function addInstantiation(string $fullyQualifiedName, string $context, string $filename, int $startLine, int $endLine)
    {
        $this->addUsage(self::USAGE_NEW, $fullyQualifiedName, $context, $filename, $startLine, $endLine);
    }



    public function addInstantiationWithVariable(string $variableName, string $context, string $filename, int $startLine, int $endLine)
    {
        $notice = array(
            'type' => self::NOTICE_NEW_WITH_VARIABLE,
            'variable' => $variableName
        );

        $this->addNotice($notice, $context, $filename, $startLine, $endLine);
    }



    public function addUnknownInstantiation(string $nodeType, string $context, string $filename, int $startLine, int $endLine)
    {
        $notice = array(
            'type' => self::NOTICE_UNKNOWN_NEW,
            'nodeType' => $nodeType
        );

        $this->addNotice($notice, $context, $filename, $startLine, $endLine);
    }



    public function addUseStatement(string $fullyQualifiedName, string $context, string $filename, int $startLine, int $endLine)
    {
        $this->addUsage(self::USAGE_USE, $fullyQualifiedName, $context, $filename, $startLine, $endLine);
    }



    public function addUnknownUseStatement(string $nodeType, string $context, string $filename, int $startLine, int $endLine)
    {
        $notice = array(
            'type' => self::NOTICE_UNKNOWN_USE,
            'nodeType' => $nodeType
        );

        $this->addNotice($notice, $context, $filename, $startLine, $endLine);
    }



    public function addTypeDeclaration(string $fullyQualifiedName, string $context, string $filename, int $startLine, int $endLine)
    {
        $this->addUsage(self::USAGE_TYPE_DECLARATION, $fullyQualifiedName, $context, $filename, $startLine, $endLine);
    }



    public function addConstantFetch(string $classFqn, string $constantName, string $context, string $filename, int $startLine, int $endLine)
    {
        $this->addUsage(self::USAGE_CONST_FETCH, $classFqn, $context, $filename, $startLine, $endLine);
    }



    public function addConstantFetchWithSelf(string $constantName, string $context, string $filename, int $startLine, int $endLine)
    {
        $classFqn = $context;
        $this->addUsage(self::USAGE_CONST_FETCH, $classFqn, $context, $filename, $startLine, $endLine);
    }



    public function addConstantFetchWithVariable(string $variableName, string $context, string $filename, int $startLine, int $endLine)
    {
        $notice = array(
            'type' => self::NOTICE_CONST_FETCH_WITH_VARIABLE,
            'variable' => $variableName
        );

        $this->addNotice($notice, $context, $filename, $startLine, $endLine);
    }



    public function addStaticCall(string $classFqn, string $methodName, array $args, string $context, string $filename, int $startLine, int $endLine)
    {
        $this->addUsage(self::USAGE_STATIC_CALL, $classFqn, $context, $filename, $startLine, $endLine);
    }



    public function addStaticCallWithVariable(string $variableName, string $methodName, array $args, string $context, string $filename, int $startLine, int $endLine)
    {
        $notice = array(
            'type' => self::NOTICE_STATC_CALL_WITH_VARIABLE,
            'variable' => $variableName
        );

        $this->addNotice($notice, $context, $filename, $startLine, $endLine);
    }



    public function getDefinitions(): array
    {
        return $this->index['definitions'];
    }



    public function getNamespaces(): array
    {
        return $this->index['namespaces'];
    }



    public function getUsages(): array
    {
        return $this->index['usages'];
    }



    public function getNotices(): array
    {
        return $this->index['notices'];
    }



    private function addUsage(string $typeOfUsage, string $fullyQualifiedName, string $context, string $filename, int $startLine, int $endLine)
    {
        $this->index['usages'][$fullyQualifiedName][$typeOfUsage][] = array(
            'context' => $context,
            'file' => $filename,
            'startLine' => $startLine,
            'endLine' => $endLine
        );
    }



    private function addNotice(array $notice, string $context, string $filename, int $startLine, int $endLine)
    {
        $notice['context'] = $context;
        $notice['file'] = $filename;
        $notice['startLine'] = $startLine;
        $notice['endLine'] = $endLine;

        $this->index['notices'][] = $notice;
    }



    private function updateNamespaceStatistik(array $nameParts, int $numLines)
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



    private function increaseDirectCountForNamespace(string $namespace)
    {
        $this->createNamespaceEntryIfNotExists($namespace);
        $this->index['namespaces'][$namespace]['countDirectDescendents'] += 1;
        $this->index['namespaces'][$namespace]['countAllDescendents'] += 1;
    }



    private function updateParentNamespace(string $namespace, string $subNamespace)
    {
        $this->createNamespaceEntryIfNotExists($namespace);
        $this->index['namespaces'][$namespace]['countAllDescendents'] += 1;

        // Here I use the key to store the array entries, because it is much faster than using unique afterwards
        $this->index['namespaces'][$namespace]['subNamespaces'][$subNamespace] = true;
    }



    private function createNamespaceEntryIfNotExists(string $namespace)
    {
        if (!array_key_exists($namespace, $this->index['namespaces'])) {
            $this->index['namespaces'][$namespace] = array(
                'name' => array(
                    'fqn' => $namespace
                ),
                'countDirectDescendents' => 0,
                'countAllDescendents' => 0,
                'subNamespaces' => array()
            );
        }
    }
}
