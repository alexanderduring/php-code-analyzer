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
        // Found "new" node
        if ($node->getType() == 'Expr_New') {
            $this->analyzeInstantiation($node);
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
    private function analyzeInstantiation(Node $newNode)
    {
        $file = 'not-implemented-yet.php';
        $line = $newNode->getLine();

        // "new" statement with fully qualified class name
        if ($newNode->class->getType() == 'Name_FullyQualified') {
            $name = implode('\\', $newNode->class->parts);
            $this->index->addInstantiation($name, $file, $line);
        }

        // "new" statement with variable
        if ($newNode->class->getType() == 'Expr_Variable') {
            $variableName = '$' . $newNode->class->name;
            $this->index->addInstantiationWithVariable($variableName, $file, $line);
        }

        // "new" statement with static class variable
        if ($newNode->class->getType() == 'Expr_StaticPropertyFetch') {
            $fetchNode = $newNode->class;
            $className = implode('\\', $fetchNode->class->parts);
            $variableName = $fetchNode->name;
            $fullName = $className . "::$" . $variableName;
            $this->index->addInstantiationWithVariable($fullName, $file, $line);
        }

    }
}