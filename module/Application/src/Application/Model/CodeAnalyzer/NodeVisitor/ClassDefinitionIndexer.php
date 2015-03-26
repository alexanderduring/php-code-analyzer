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
            $this->addClassToIndex($node);
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
     * Adds the fully qualified class name to the index.
     * @param Node $classStatement
     */
    private function addClassToIndex(Node $classStatement)
    {
        $className = $classStatement->name;
        $fullyQualifiedClassName = implode('\\', $classStatement->namespacedName->parts);
        $this->index->addClass($fullyQualifiedClassName);
    }
}