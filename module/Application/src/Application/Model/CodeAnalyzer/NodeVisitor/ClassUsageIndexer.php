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
    /** @var Index */
    private $index;

    private $context = array('global');



    /**
     * @param Index $index
     * @return void
     */
    public function injectIndex(Index $index)
    {
        $this->index = $index;
    }



    /**
     * @param array $nodes
     * @return void
     */
    public function beforeTraverse(array $nodes)
    {
    }



    /**
     * @param \PhpParser\Node $node
     * @return void
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

        // Found "use" statement
        if ($node->getType() == 'Stmt_Use') {
            $this->analyzeUseStatement($node);
        }

        // Found class method
        if ($node->getType() == 'Stmt_ClassMethod') {
            $this->analyzeClassMethod($node);
        }

        // Found usage of class constant
        if ($node->getType() == 'Expr_ClassConstFetch') {
            $this->analyzeClassConstant($node);
        }

        // Found usage in static call
        if ($node->getType() == 'Expr_StaticCall') {
            $this->analyzeStaticCall($node);
        }

        $this->index->addNodeType($node->getType());
    }


    /**
     * @param Node $node
     * @return void
     */
    public function leaveNode(Node $node)
    {
        if (in_array($node->getType(), array('Stmt_Class', 'Stmt_Interface'))) {
            array_shift($this->context);
        }
    }



    /**
     * @param array $nodes
     * @return void
     */
    public function afterTraverse(array $nodes)
    {

    }



    /**
     * @param Node $newNode
     * @return void
     */
    private function analyzeInstantiation(Node $newNode)
    {
        $startLine = $newNode->getAttribute('startLine');
        $endLine = $newNode->getAttribute('endLine');
        $classNode = $newNode->class;

        switch ($classNode->getType()) {

            // "new" statement with fully qualified class name
            case 'Name_FullyQualified':
                $name = implode('\\', $classNode->parts);
                $this->index->addInstantiation($name, $this->getContext(), $startLine, $endLine);
                break;

            // "new" statement with variable
            case 'Expr_Variable':
                $variableName = '$' . $classNode->name;
                $this->index->addInstantiationWithVariable($variableName, $this->getContext(), $startLine, $endLine);
                break;

            // "new" statement with static class variable
            case 'Expr_StaticPropertyFetch':
                $fetchNode = $classNode;
                $className = implode('\\', $fetchNode->class->parts);
                $variableName = $fetchNode->name;
                $fullName = $className . "::$" . $variableName;
                $this->index->addInstantiationWithVariable($fullName, $this->getContext(), $startLine, $endLine);
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
                $this->index->addInstantiationWithVariable($fullName, $this->getContext(), $startLine, $endLine);
                break;

            default:
                $this->index->addUnknownInstantiation($classNode->getType(), $this->getContext(), $startLine, $endLine);
        }
    }



    private function analyzeUseStatement(Node $node)
    {

        foreach ($node->uses as $use) {
            $startLine = $use->getAttribute('startLine');
            $endLine = $use->getAttribute('endLine');

            if ($use instanceof Node\Stmt\UseUse) {
                $nameNode = $use->name;
                $className = implode('\\', $nameNode->parts);
                $this->index->addUseStatement($className, $this->getContext(), $startLine, $endLine);
            } else {
                $this->index->addUnknownUseStatement($use->getType(), $this->getContext(), $startLine, $endLine);
            }
        }
    }



    private function analyzeClassMethod(Node $node)
    {
        $parameters = $node->params;
        foreach ($parameters as $parameter) {
            if (!in_array($parameter->type, ['array', 'callable', 'bool', 'float', 'int', 'string', 'self', ''])) {

                $startLine = $parameter->getAttribute('startLine');
                $endLine = $parameter->getAttribute('endLine');

                $this->index->addTypeDeclaration($parameter->type->toString(), $this->getContext(), $startLine, $endLine);
            }
        }
    }



    private function analyzeClassConstant(Node\Expr\ClassConstFetch $classConstFetch)
    {
        $startLine = $classConstFetch->getAttribute('startLine');
        $endLine = $classConstFetch->getAttribute('endLine');
        $classNode = $classConstFetch->class;

        switch ($classNode->getType()) {
            // class constant statement with variable
            case 'Expr_Variable':
                $variableName = '$' . $classNode->name;
                $this->index->addConstantFetchWithVariable($variableName, $this->getContext(), $startLine, $endLine);
                break;

            case 'Name_FullyQualified':
                $className = implode('\\', $classNode->parts);
                $constName = $classConstFetch->name;
                $this->index->addConstantFetch($className, $constName, $this->getContext(), $startLine, $endLine);
                break;

            case 'Name':
                $className = implode('\\', $classNode->parts);
                $constName = $classConstFetch->name;

                // @todo: $className could also be 'parent'.
                if ($className !== 'self') {
                    $this->index->addConstantFetch($className, $constName, $this->getContext(), $startLine, $endLine);
                } else {
                    $this->index->addConstantFetchWithSelf($constName, $this->getContext(), $startLine, $endLine);
                }
                break;

            default:
                echo 'Unknown type of const fetch: '. $classNode->getType() . PHP_EOL;
                var_export($classConstFetch);
        }


    }



    private function analyzeStaticCall(Node\Expr\StaticCall $staticCall)
    {
        $startLine = $staticCall->getAttribute('startLine');
        $endLine = $staticCall->getAttribute('endLine');
        $classNode = $staticCall->class;

        switch ($classNode->getType()) {
            case 'Expr_Variable':
                $variableName = '$' . $classNode->name;
                $methodName = $staticCall->name;
                $args = $staticCall->args;
                $this->index->addStaticCallWithVariable($variableName, $methodName, $args, $this->getContext(), $startLine, $endLine);
                break;

            default:
                // This could be Name_FullyQualified or Name or may be other things.
                $className = implode('\\', $classNode->parts);

                if ($className !== 'parent') {
                    $methodName = $staticCall->name;
                    $args = $staticCall->args;

                    $this->index->addStaticCall($className, $methodName, $args, $this->getContext(), $startLine, $endLine);
                }
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
