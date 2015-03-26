<?php

namespace Application\Model\CodeAnalyzer;

use Application\Model\CodeAnalyzer\NodeVisitor\ClassDefinitionIndexer;
use PhpParser\Error as PhpParserError;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;

// Comment in for debugging with var_dump()
//ini_set('xdebug.var_display_max_depth', 5);
//ini_set('xdebug.var_display_max_children', 256);
//ini_set('xdebug.var_display_max_data', 1024);

/**
 * At the moment this class wraps the whole
 * Analyzer Logic. Certainly it will have to be split up
 * into smaller parts in the future.
 */
class CodeAnalyzer
{
    /** @var Application\Model\CodeAnalyzer\Index */
    private $index;

    private $classes = array();
    private $assignments = array();
    private $newExpressions = array();



    public function analyze($code)
    {
        // Should be injected via factory
        $parser = new Parser(new Lexer());
        $traverser = new NodeTraverser();

        // Add NameResolver to handle namespaces
        $nameResolver = new NameResolver();
        $traverser->addVisitor($nameResolver);

        // Add our class definition indexer
        $this->index = new Index();
        $classDefinitionIndexer = new ClassDefinitionIndexer();
        $classDefinitionIndexer->injectIndex($this->index);
        $traverser->addVisitor($classDefinitionIndexer);

        try {
            $nodes = $parser->parse($code);
//            var_dump($nodes);
            $traversedNodes = $traverser->traverse($nodes);
//            var_dump($traversedNodes);
        }
        catch (PhpParserError $exception) {
            echo 'Parse Error: ', $exception->getMessage();
        }

        // Will be moved into visitor
        foreach ($traversedNodes as $node) {
            $this->checkNode($node);
        }

        $this->report();
    }



    /**
     * @param Node $node
     */
    private function checkNode(Node $node)
    {
        if ($node->getType() == 'Stmt_Class') {
            $className = $node->name;
            $class = array(
                'name' => $className,
            );
            $this->classes[$className] = $class;
        }

        if ($node->getType() == 'Expr_Assign') {
            $this->assignments[] = $node;
            $this->checkNode($node->expr);
        }

        if ($node->getType() == 'Expr_New') {
            $this->newExpressions[] = $node;
        }
    }



    private function report()
    {
        echo $this->index . "\n";

        echo "Classes\n";
        echo "-------\n";

        foreach ($this->classes as $class) {

            echo $class['name'] . "\n";
        }

        echo "\n";
        echo "Instantiations\n";
        echo "--------------\n";

        foreach ($this->newExpressions as $new) {

            $classNameNode = $new->class;
            $className = implode('\\', $classNameNode->parts);

            if (array_key_exists($className, $this->classes)) {
                $classIsKnown = true;
            } else {
                $classIsKnown = false;
            }

            $classIsKnownText = $classIsKnown ? '' : ' (unbekannt)';

            $lineNumber = $new->getLine();
            echo "Class " . $className . $classIsKnownText .  ", Zeile " . $lineNumber . "\n";
        }
    }
}