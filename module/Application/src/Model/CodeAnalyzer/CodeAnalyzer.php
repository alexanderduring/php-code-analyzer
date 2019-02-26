<?php

namespace Application\Model\CodeAnalyzer;

use Application\Model\CodeAnalyzer\NodeTraverser\ContextAwareNodeTraverser;
use PhpParser\Error as PhpParserError;
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
    /** @var \Application\Model\CodeAnalyzer\Index */
    private $index;

    /** @var \PhpParser\Parser */
    private $parser;

    /** @var ContextAwareNodeTraverser */
    private $traverser;



    public function injectParser(Parser $parser)
    {
        $this->parser = $parser;
    }



    public function injectTraverser(ContextAwareNodeTraverser $traverser)
    {
        $this->traverser = $traverser;
    }



    public function injectIndex(Index $index)
    {
        $this->index = $index;
    }



    public function getIndex()
    {
        return $this->index;
    }



    public function analyze(string $code, string $sourceName)
    {
        try {
            $nodes = $this->parser->parse($code);
            $this->traverser->setFilename($sourceName);
            $this->traverser->traverse($nodes);
        }
        catch (PhpParserError $exception) {
            echo 'Parse Error: ', $exception->getMessage();
        }
    }
}
