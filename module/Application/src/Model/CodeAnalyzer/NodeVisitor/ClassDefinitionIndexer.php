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
            $this->addInterfaceToIndex($node);
        }
    }



    public function leaveNode(Node $node)
    {
    }



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



    private function addClassToIndex(Node $classStatement)
    {
        $this->addEntryToIndex($classStatement, 'class');
    }



    private function addAbstractClassToIndex(Node $classStatement)
    {
        $this->addEntryToIndex($classStatement, 'abstract class');
    }



    private function addAnonymousClassToIndex(Node $classStatement)
    {
        $this->addEntryToIndex($classStatement, 'anonymous class');
    }



    private function addFinalClassToIndex(Node $classStatement)
    {
        $this->addEntryToIndex($classStatement, 'final class');
    }



    private function addInterfaceToIndex(Node $interfaceStatement)
    {
        $this->addEntryToIndex($interfaceStatement, 'interface');
    }



    private function addEntryToIndex(Node $node, string $type)
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
            $extendedClass = [];
        }

        // Implemented interfaces
        if (property_exists($node, 'implements')) {

            /** @var Node\Name[] $implementsNodes */
            $implementsNodes = $node->implements;

            $implementedInterfaces = array();
            foreach($implementsNodes as $implementsNode) {
                $interface = array(
                    'name' => array(
                        'fqn' => $implementsNode->toString(),
                        'parts' => $implementsNode->parts
                    )
                );
                $implementedInterfaces[] = $interface;
            }
        } else {
            $implementedInterfaces = [];
        }

        $this->index->addClass($nameParts, $type, $extendedClass, $implementedInterfaces, $this->filename, $startLine, $endLine);
    }
}
