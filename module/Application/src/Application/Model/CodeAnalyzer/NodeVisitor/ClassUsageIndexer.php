<?php

namespace Application\Model\CodeAnalyzer\NodeVisitor;

use Application\Model\CodeAnalyzer\Index;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

/**
 * This visitor looks for class usages
 * and stores them in an index.
 */
class ClassUsageIndexer extends NodeVisitorAbstract
{
    /** @var \Application\Model\CodeAnalyzer\Index */
    private $index;

    private $context = array('global');



    /**
     * @param \Application\Model\CodeAnalyzer\Index $index
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
            $fullyQualifiedClassName = implode('\\', $node->namespacedName->parts);
            $context = $fullyQualifiedClassName;
            array_unshift($this->context, $context);
        }

        if ($node->getType() == 'Stmt_Interface') {
            $fullyQualifiedClassName = implode('\\', $node->namespacedName->parts);
            $context = $fullyQualifiedClassName;
            array_unshift($this->context, $context);
        }

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
        if (in_array($node->getType(), array('Stmt_Class', 'Stmt_Interface'))) {
            array_shift($this->context);
        }
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
                $this->index->addInstantiation($name, $this->getContext(), $line);
                break;

            // "new" statement with variable
            case 'Expr_Variable':
                $variableName = '$' . $classNode->name;
                $this->index->addInstantiationWithVariable($variableName, $this->getContext(), $line);
                break;

            // "new" statement with static class variable
            case 'Expr_StaticPropertyFetch':
                $fetchNode = $classNode;
                $className = implode('\\', $fetchNode->class->parts);
                $variableName = $fetchNode->name;
                $fullName = $className . "::$" . $variableName;
                $this->index->addInstantiationWithVariable($fullName, $this->getContext(), $line);
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
                $this->index->addInstantiationWithVariable($fullName, $this->getContext(), $line);
                break;

            default:
                $this->index->addUnknownInstantiation($classNode->getType(), $this->getContext(), $line);
        }
    }



    /**
     * @return string
     */
    private function getContext()
    {
        return $this->context[0];
    }
}