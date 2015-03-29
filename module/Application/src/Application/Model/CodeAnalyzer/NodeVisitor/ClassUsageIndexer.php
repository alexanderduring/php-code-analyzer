<?php

namespace Application\Model\CodeAnalyzer\NodeVisitor;

use Application\Model\CodeAnalyzer\UsageIndex;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

/**
 * This visitor looks for class usages
 * and stores them in an index.
 */
class ClassUsageIndexer extends NodeVisitorAbstract
{
    /** @var Application\Model\CodeAnalyzer\UsageIndex */
    private $index;



    /**
     * @param Application\Model\CodeAnalyzer\UsageIndex $index
     */
    public function injectIndex(UsageIndex $index)
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
        if ($node->getType() == 'Expr_New') {
            $this->addInstantiation($node);
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
     * @param Node $newNode
     */
    private function addInstantiation(Node $newNode)
    {
        $name = implode('\\', $newNode->class->parts);
        $file = 'not-implemented-yet.php';
        $line = $newNode->getLine();
        $this->index->addInstantiation($name, $file, $line);
    }
}