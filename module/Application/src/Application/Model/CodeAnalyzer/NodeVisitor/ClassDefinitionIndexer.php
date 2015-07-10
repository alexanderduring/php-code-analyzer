<?php

namespace Application\Model\CodeAnalyzer\NodeVisitor;

use Application\Model\CodeAnalyzer\Index;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

/**
 * This visitor looks for class definitions
 * and stores them in an index.
 */
class ClassDefinitionIndexer extends NodeVisitorAbstract
{
    /** @var Application\Model\CodeAnalyzer\Index */
    private $index;



    /**
     * @param Application\Model\CodeAnalyzer\Index $index
     */
    public function injectIndex(Index $index)
    {
        $this->index = $index;
    }



    /**
     * @param array $nodes
     */
    public function beforeTraverse(array $nodes)
    {
    }



    /**
     * @param Node $node
     */
    public function enterNode(Node $node)
    {
        if ($node->getType() == 'Stmt_Class') {
            if ($node->isAbstract()) {
                $this->addAbstractClassToIndex($node);
            }
            elseif ($node->isFinal()) {
                $this->addFinalClassToIndex($node);
            }
            elseif (!$node->isAbstract() && !$node->isAbstract()) {
                $this->addClassToIndex($node);
            }
            else {
                throw new Exception('Found abstract final class');
            }
        }

        if ($node->getType() == 'Stmt_Interface') {
            $this->addInterfaceToIndex($node);
        }
    }



    /**
     * @param Node $node
     */
    public function leaveNode(Node $node)
    {
    }



    /**
     * @param array $nodes
     */
    public function afterTraverse(array $nodes)
    {

    }



    /**
     * Adds a class to the index.
     * @param Node $classStatement
     */
    private function addClassToIndex(Node $classStatement)
    {
        $this->addEntryToIndex($classStatement, 'class');
    }



    /**
     * Adds an abstract class to the index.
     * @param Node $classStatement
     */
    private function addAbstractClassToIndex(Node $classStatement)
    {
        $this->addEntryToIndex($classStatement, 'abstract class');
    }



    /**
     * Adds a final class to the index.
     * @param Node $classStatement
     */
    private function addFinalClassToIndex(Node $classStatement)
    {
        $this->addEntryToIndex($classStatement, 'final class');
    }



    /**
     * Adds an interface to the index.
     * @param Node $interfaceStatement
     */
    private function addInterfaceToIndex(Node $interfaceStatement)
    {
        $this->addEntryToIndex($interfaceStatement, 'interface');
    }



    /**
     * Adds a class, abstract class or interface to the index.
     * @param Node $node
     */
    private function addEntryToIndex(Node $node, $type)
    {
        $fullyQualifiedClassName = implode('\\', $node->namespacedName->parts);
        $startLine = $node->getAttribute('startLine');
        $endLine = $node->getAttribute('endLine');
        $this->index->addClass($fullyQualifiedClassName, $type, $startLine, $endLine);
    }
}