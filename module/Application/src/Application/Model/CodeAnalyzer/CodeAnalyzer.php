<?php

namespace Application\Model\CodeAnalyzer;

use Application\Model\CodeAnalyzer\NodeVisitor\ClassDefinitionIndexer;
use Application\Model\CodeAnalyzer\NodeVisitor\ClassUsageIndexer;
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
    /** @var Application\Model\CodeAnalyzer\DefinitionIndex */
    private $definitionIndex;

    /** @var Application\Model\CodeAnalyzer\UsageIndex */
    private $usageIndex;



    /**
     * @param string $code
     */
    public function analyze($code)
    {
        // Should be injected via factory
        $parser = new Parser(new Lexer());
        $traverser = new NodeTraverser();

        // Add NameResolver to handle namespaces
        $nameResolver = new NameResolver();
        $traverser->addVisitor($nameResolver);

        // Add our class definition indexer
        $this->definitionIndex = new DefinitionIndex();
        $classDefinitionIndexer = new ClassDefinitionIndexer();
        $classDefinitionIndexer->injectIndex($this->definitionIndex);
        $traverser->addVisitor($classDefinitionIndexer);

        // Add our class usage indexer
        $this->usageIndex = new UsageIndex();
        $classUsageIndexer = new ClassUsageIndexer();
        $classUsageIndexer->injectIndex($this->usageIndex);
        $traverser->addVisitor($classUsageIndexer);

        try {
            $nodes = $parser->parse($code);
//            var_dump($nodes);
            $traverser->traverse($nodes);
//            var_dump($traversedNodes);
        }
        catch (PhpParserError $exception) {
            echo 'Parse Error: ', $exception->getMessage();
        }

        $this->report();
    }



    private function report()
    {
        echo "\nFound classes:\n--------------\n";
        echo $this->definitionIndex . "\n";

        echo "\n";
        echo "Found instantiations:\n";
        echo "---------------------\n";
        echo $this->usageIndex . "\n";
    }
}