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
        $classNode = $newNode->class;

        switch ($classNode->getType()) {

            // "new" statement with fully qualified class name
            case 'Name_FullyQualified':
                $name = implode('\\', $classNode->parts);
                $this->index->addInstantiation($name, $file, $line);
                break;

            // "new" statement with variable
            case 'Expr_Variable':
                $variableName = '$' . $classNode->name;
                $this->index->addInstantiationWithVariable($variableName, $file, $line);
                break;

            // "new" statement with static class variable
            case 'Expr_StaticPropertyFetch':
                $fetchNode = $classNode;
                $className = implode('\\', $fetchNode->class->parts);
                $variableName = $fetchNode->name;
                $fullName = $className . "::$" . $variableName;
                $this->index->addInstantiationWithVariable($fullName, $file, $line);
                break;

            default:
                $this->index->addUnknownInstantiation($classNode->getType(), $file, $line);
        }
    }
}