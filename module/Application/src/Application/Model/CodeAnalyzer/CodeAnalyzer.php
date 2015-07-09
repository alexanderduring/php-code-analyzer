<?php

namespace Application\Model\CodeAnalyzer;

use PhpParser\Error as PhpParserError;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;

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

    /** @var \PhpParser\NodeTraverser */
    private $traverser;



    /**
     * @param \PhpParser\Parser $parser
     */
    public function injectParser(Parser $parser)
    {
        $this->parser = $parser;
    }



    /**
     * @param \PhpParser\NodeTraverser $traverser
     */
    public function injectTraverser(NodeTraverser $traverser)
    {
        $this->traverser = $traverser;
    }



    /**
     * @param \Application\Model\CodeAnalyzer\Index $index
     */
    public function injectIndex(Index $index)
    {
        $this->index = $index;
    }



    /**
     * @return \Application\Model\CodeAnalyzer\Index
     */
    public function getIndex()
    {
        return $this->index;
    }



    /**
     * @param string $path
     */
    public function process($path, $ignores)
    {
        $realPath = realpath($path);

        if (is_dir($realPath)) {
            $this->processDirectory($realPath, $ignores);
        } else {
            $this->processFile($realPath);
        }
    }



    /**
     * @param string $path
     */
    private function processFile($path)
    {
        $file = new SplFileInfo($path);
        $this->foo($file, $path);
    }



    /**
     * @param string $path
     */
    private function processDirectory($path, $ignores)
    {
        // iterate over all .php files in the directory
        $directoryIterator = new RecursiveDirectoryIterator($path);
        $filterIterator = new RecursiveFilterIterator($directoryIterator);
        $filterIterator->setIgnores($ignores);
        $iterator = new RecursiveIteratorIterator($filterIterator);
        $files = new RegexIterator($iterator, '/\.php$/');

        foreach ($files as $file) {
            $this->foo($file, $path);
        }
    }



    /**
     * @todo Find a good name for this method
     * @param SplFileInfo $file
     */
    private function foo(SplFileInfo $file, $path)
    {
        $filename = ltrim(str_replace($path, '', $file->getPathName()), '/');
        $code = file_get_contents($file);

        $this->analyze($filename, $code);
    }



    /**
     * @param string $filename
     * @param string $code
     */
    private function analyze($filename, $code)
    {
        $this->index->setFilename($filename);

        try {
            $nodes = $this->parser->parse($code);
            $this->traverser->traverse($nodes);
        }
        catch (PhpParserError $exception) {
            echo 'Parse Error: ', $exception->getMessage();
        }
    }
}
