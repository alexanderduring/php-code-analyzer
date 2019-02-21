<?php

namespace Application\Model\CodeAnalyzer\NodeVisitor;

use Application\Model\CodeAnalyzer\Index;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_ as StmtClassNode;

/**
 * This visitor looks for class definitions
 * and stores them in an index.
 */
class ClassDefinitionIndexer extends NodeVisitorAbstract
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



    /**
     * @param StmtClassNode $stmtClassNode
     */
    private function enterNodeStmtClass(StmtClassNode $stmtClassNode)
    {
        if ($stmtClassNode->isAbstract()) {
            $this->addAbstractClassToIndex($stmtClassNode);
        }
        elseif ($stmtClassNode->isFinal()) {
            $this->addFinalClassToIndex($stmtClassNode);
        }
        elseif (!$stmtClassNode->isAbstract() && !$stmtClassNode->isAbstract()) {
            $this->addClassToIndex($stmtClassNode);
        }
        else {
            throw new Exception('Found abstract final class');
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
        if (property_exists($node, 'namespacedName')) {
            $nameParts = $node->namespacedName->parts;

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

            $startLine = $node->getAttribute('startLine');
            $endLine = $node->getAttribute('endLine');
            $this->index->addClass($nameParts, $type, $extendedClass, $implementedInterfaces, $startLine, $endLine);
        } else {
            print_r($node);
            exit("Found class definition without name.");
        }
    }
}
