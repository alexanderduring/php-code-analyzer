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
        $line = $newNode->getLine();
        $classNode = $newNode->class;

        switch ($classNode->getType()) {

            // "new" statement with fully qualified class name
            case 'Name_FullyQualified':
                $name = implode('\\', $classNode->parts);
                $this->index->addInstantiation($name, $line);
                break;

            // "new" statement with variable
            case 'Expr_Variable':
                $variableName = '$' . $classNode->name;
                $this->index->addInstantiationWithVariable($variableName, $line);
                break;

            // "new" statement with static class variable
            case 'Expr_StaticPropertyFetch':
                $fetchNode = $classNode;
                $className = implode('\\', $fetchNode->class->parts);
                $variableName = $fetchNode->name;
                $fullName = $className . "::$" . $variableName;
                $this->index->addInstantiationWithVariable($fullName, $line);
                break;

            // "new" statement on array entry
            case 'Expr_ArrayDimFetch':
                /*
                    Example:
                    $instance = new $classes['third'];


                    object(PhpParser\Node\Expr\ArrayDimFetch)#280 (4) {
                        ["var"] => object(PhpParser\Node\Expr\Variable)#282 (3) {
                            ["name"] => string(7) "classes"
                            ["subNodeNames":"PhpParser\NodeAbstract":private] => NULL
                            ["attributes":protected] => array(2) {
                                ["startLine"] => int(14)
                                ["endLine"] => int(14)
                            }
                        }
                        ["dim"] => object(PhpParser\Node\Scalar\String_)#281 (3) {
                            ["value"] => string(5) "third"
                            ["subNodeNames":"PhpParser\NodeAbstract":private] => NULL
                            ["attributes":protected] => array(2) {
                                ["startLine"] => int(14)
                                ["endLine"] => int(14)
                            }
                        }
                        ["subNodeNames":"PhpParser\NodeAbstract":private] => NULL
                        ["attributes":protected] => array(2) {
                            ["startLine"] => int(14)
                            ["endLine"] => int(14)
                        }
                    }
                */
                $fetchNode = $classNode;
                $variableName = '$' . $fetchNode->var->name;
                $dimension = $fetchNode->dim->value;
                $fullName = $variableName . "['" . $dimension . "']";
                $this->index->addInstantiationWithVariable($fullName, $line);
                break;

            default:
                $this->index->addUnknownInstantiation($classNode->getType(), $line);
        }
    }
}