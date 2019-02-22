<?php

namespace Application\Model\CodeAnalyzer\NodeVisitor;

use Application\Model\CodeAnalyzer\Index;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_ as StmtClassNode;

/**
 * This visitor looks for class definitions
 * and stores them in an index.
 */
class ClassDefinitionIndexer extends ContextAwareNodeVisitor
{
    /** @var \Application\Model\CodeAnalyzer\Index */
    private $index;



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
     * @param \PhpParser\Node $node
     */
    public function enterNode(Node $node)
    {
        if ($node->getType() == 'Stmt_Class') {
            $this->enterNodeStmtClass($node);
        }

        if ($node->getType() == 'Stmt_Interface') {
            $this->addInterfaceToIndex($node);
        }
    }



    /**
     * @param \PhpParser\Node $node
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



    private function enterNodeStmtClass(StmtClassNode $stmtClassNode)
    {
        if ($stmtClassNode->isAbstract()) {
            $this->addAbstractClassToIndex($stmtClassNode);
        }
        elseif ($stmtClassNode->isFinal()) {
            $this->addFinalClassToIndex($stmtClassNode);
        }
        elseif ($stmtClassNode->isAnonymous()) {
            $this->addAnonymousClassToIndex($stmtClassNode);
        }
        else {
            $this->addClassToIndex($stmtClassNode);
        }
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



    private function addAnonymousClassToIndex(Node $classStatement)
    {
        $this->addEntryToIndex($classStatement, 'anonymous class');
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
        $startLine = $node->getAttribute('startLine');
        $endLine = $node->getAttribute('endLine');

        if (property_exists($node, 'namespacedName')) {
            $nameParts = $node->namespacedName->parts;
        } else {
            // If the class has no name we use the filename and line number
            $escapedFilename = str_replace('/', '.', $this->filename);
            $nameParts = ['__Anonymous', $escapedFilename, $startLine];
        }

        // Extended classes
        $extendsNode = $node->extends;
        if ($extendsNode instanceof Node\Name\FullyQualified) {
            $extendedClass = array(
                'name' => array(
                    'fqn' => $extendsNode->toString(),
                    'parts' => $extendsNode->parts
                )
            );
        } else {
            $extendedClass = null;
        }

        // Implemented interfaces
        if (property_exists($node, 'implements')) {
            $implementedInterfaces = array();
            foreach($node->implements as $implementsNode) {
                $interface = array(
                    'name' => array(
                        'fqn' => $implementsNode->toString(),
                        'parts' => $implementsNode->parts
                    )
                );
                $implementedInterfaces[] = $interface;
            }
            $implementedInterfaces = empty($implementedInterfaces) ? null : $implementedInterfaces;
        } else {
            $implementedInterfaces = null;
        }

        $this->index->addClass($nameParts, $type, $extendedClass, $implementedInterfaces, $this->filename, $startLine, $endLine);
    }
}
