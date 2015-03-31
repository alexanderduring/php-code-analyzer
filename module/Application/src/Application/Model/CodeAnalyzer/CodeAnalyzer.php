<?php

namespace Application\Model\CodeAnalyzer;

use PhpParser\Error as PhpParserError;
use PhpParser\NodeTraverser;
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

    /** @var PhpParser\Parser */
    private $parser;

    /** @var PhpParser\NodeTraverser */
    private $traverser;



    /**
     * @param PhpParser\Parser $parser
     */
    public function injectParser(Parser $parser)
    {
        $this->parser = $parser;
    }



    /**
     * @param PhpParser\NodeTraverser $traverser
     */
    public function injectTraverser(NodeTraverser $traverser)
    {
        $this->traverser = $traverser;
    }



    /**
     * @param Application\Model\CodeAnalyzer\DefinitionIndex $index
     */
    public function injectDefinitionIndex(DefinitionIndex $index)
    {
        $this->definitionIndex = $index;
    }



    /**
     * @param Application\Model\CodeAnalyzer\UsageIndex $index
     */
    public function injectUsageIndex(UsageIndex $index)
    {
        $this->usageIndex = $index;
    }



    /**
     * @param string $code
     */
    public function analyze($code)
    {
        try {
            $nodes = $this->parser->parse($code);
//            var_dump($nodes);
            $this->traverser->traverse($nodes);
//            var_dump($traversedNodes);
        }
        catch (PhpParserError $exception) {
            echo 'Parse Error: ', $exception->getMessage();
        }

        $this->report();
    }



    private function report()
    {
        echo $this->definitionIndex . "\n";

        echo $this->usageIndex . "\n";
    }
}
