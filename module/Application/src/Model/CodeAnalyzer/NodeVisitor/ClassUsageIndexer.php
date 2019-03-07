<?php

namespace Application\Model\CodeAnalyzer\NodeVisitor;

use Application\Model\CodeAnalyzer\Index;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_ as StmtClassNode;
use PhpParser\Node\Expr\New_ as ExprNewNode;

/**
 * This visitor looks for class usages
 * and stores them in an index.
 */
class ClassUsageIndexer extends ContextAwareNodeVisitor
{
    /** @var Index */
    private $index;



    public function injectIndex(Index $index)
    {
        $this->index = $index;
    }



    public function beforeTraverse(array $nodes)
    {
    }



    public function enterNode(Node $node)
    {
        if ($node->getType() == 'Stmt_Class') {
            $this->enterNodeStmtClass($node);
        }

        if ($node->getType() == 'Stmt_Interface') {
            $this->context->enterClass($node->namespacedName->parts);
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
    }



    private function enterNodeStmtClass(StmtClassNode $stmtClassNode)
    {
        if (property_exists($stmtClassNode, 'namespacedName')) {
            $nameParts = $stmtClassNode->namespacedName->parts;
        } else {
            // If the class has no name we use the filename and line number
            $startLine = $stmtClassNode->getAttribute('startLine');
            $escapedFilename = str_replace('\\', '.', $this->context->getFileName());
            $nameParts = ['__Anonymous', $escapedFilename, $startLine];
        }

        $this->context->enterClass($nameParts);
    }



    public function leaveNode(Node $node)
    {
        if (in_array($node->getType(), array('Stmt_Class', 'Stmt_Interface'))) {
            $this->context->leaveClass();
        }
    }



    public function afterTraverse(array $nodes)
    {
    }



    private function analyzeInstantiation(ExprNewNode $newNode)
    {
        $fileName = $this->context->getFileName();
        $startLine = $newNode->getAttribute('startLine');
        $endLine = $newNode->getAttribute('endLine');
        $classNode = $newNode->class;

        $contextClass = $this->context->getClass();
        switch ($classNode->getType()) {

            // "new" statement with fully qualified class name
            case 'Name_FullyQualified':
                $name = implode('\\', $classNode->parts);
                $this->index->addInstantiation($name, $contextClass, $fileName, $startLine, $endLine);
                break;

            // "new" statement with variable
            case 'Expr_Variable':
                $variableName = '$' . $classNode->name;
                $this->index->addInstantiationWithVariable($variableName, $contextClass, $fileName, $startLine, $endLine);
                break;

            // "new" statement with static class variable
            case 'Expr_StaticPropertyFetch':
                $fetchNode = $classNode;
                $className = implode('\\', $fetchNode->class->parts);
                $variableName = $fetchNode->name;
                $fullName = $className . "::$" . $variableName;
                $this->index->addInstantiationWithVariable($fullName, $contextClass, $fileName, $startLine, $endLine);
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
                $this->index->addInstantiationWithVariable($fullName, $contextClass, $fileName, $startLine, $endLine);
                break;

            case 'Name':
                /** @var Node\Name $classNode */
                if ($classNode->isSpecialClassName()) {
                    switch ($classNode) {
                        case 'self':
                            $this->index->addInstantiation($contextClass, $contextClass, $fileName, $startLine, $endLine);
                            break;

                        default:
                            echo "new $classNode (context: {$contextClass}).\n";
                    }
                }
                break;


            default:
                $this->index->addUnknownInstantiation($classNode->getType(), $contextClass, $fileName, $startLine, $endLine);
        }
    }



    private function analyzeUseStatement(Node $node)
    {
        $fileName = $this->context->getFileName();
        $contextClass = $this->context->getClass();

        foreach ($node->uses as $use) {
            $startLine = $use->getAttribute('startLine');
            $endLine = $use->getAttribute('endLine');

            if ($use instanceof Node\Stmt\UseUse) {
                $nameNode = $use->name;
                $className = implode('\\', $nameNode->parts);
                $this->index->addUseStatement($className, $contextClass, $fileName, $startLine, $endLine);
            } else {
                $this->index->addUnknownUseStatement($use->getType(), $contextClass, $fileName, $startLine, $endLine);
            }
        }
    }



    private function analyzeClassMethod(Node $node)
    {
        $fileName = $this->context->getFileName();
        $contextClass = $this->context->getClass();

        $parameters = $node->params;
        foreach ($parameters as $parameter) {
            if (!in_array($parameter->type, ['array', 'callable', 'bool', 'float', 'int', 'string', 'self', ''])) {

                $startLine = $parameter->getAttribute('startLine');
                $endLine = $parameter->getAttribute('endLine');

                $this->index->addTypeDeclaration($parameter->type->toString(), $contextClass, $fileName, $startLine, $endLine);
            }
        }
    }



    private function analyzeClassConstant(Node\Expr\ClassConstFetch $classConstFetch)
    {
        $fileName = $this->context->getFileName();
        $contextClass = $this->context->getClass();
        $startLine = $classConstFetch->getAttribute('startLine');
        $endLine = $classConstFetch->getAttribute('endLine');
        $classNode = $classConstFetch->class;

        switch ($classNode->getType()) {
            // class constant statement with variable
            case 'Expr_Variable':
                $variableName = '$' . $classNode->name;
                $this->index->addConstantFetchWithVariable($variableName, $contextClass, $fileName, $startLine, $endLine);
                break;

            case 'Name_FullyQualified':
                $className = implode('\\', $classNode->parts);
                $constName = $classConstFetch->name;
                $this->index->addConstantFetch($className, $constName, $contextClass, $fileName, $startLine, $endLine);
                break;

            case 'Name':
                $className = implode('\\', $classNode->parts);
                $constName = $classConstFetch->name;

                // @todo: $className could also be 'parent'.
                if ($className !== 'self') {
                    $this->index->addConstantFetch($className, $constName, $contextClass, $fileName, $startLine, $endLine);
                } else {
                    $this->index->addConstantFetchWithSelf($constName, $contextClass, $fileName, $startLine, $endLine);
                }
                break;

            default:
                echo 'Unknown type of const fetch: '. $classNode->getType() . PHP_EOL;
                var_export($classConstFetch);
        }
    }



    private function analyzeStaticCall(Node\Expr\StaticCall $staticCall)
    {
        $fileName = $this->context->getFileName();
        $startLine = $staticCall->getAttribute('startLine');
        $endLine = $staticCall->getAttribute('endLine');
        $classNode = $staticCall->class;
        $contextClass = $this->context->getClass();

        switch ($classNode->getType()) {
            case 'Expr_Variable':
                $variableName = '$' . $classNode->name;
                $methodName = $staticCall->name;
                $args = $staticCall->args;
                $this->index->addStaticCallWithVariable($variableName, $methodName, $args, $contextClass, $fileName, $startLine, $endLine);
                break;

            default:
                // This could be Name_FullyQualified or Name or may be other things.
                $className = implode('\\', $classNode->parts);

                if ($className !== 'parent') {
                    $methodName = $staticCall->name;
                    $args = $staticCall->args;

                    $this->index->addStaticCall($className, $methodName, $args, $contextClass, $fileName, $startLine, $endLine);
                }
        }
    }
}
