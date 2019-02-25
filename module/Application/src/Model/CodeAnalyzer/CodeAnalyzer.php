<?php

namespace Application\Model\CodeAnalyzer;

use Application\Model\CodeAnalyzer\NodeTraverser\ContextAwareNodeTraverser;
use Application\Model\File\RecursiveFileIterator;
use Application\Model\File\RecursiveFilterIterator;
use PhpParser\Error as PhpParserError;
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

    /** @var RecursiveFileIterator */
    private $recursiveFileIterator;

    /** @var ContextAwareNodeTraverser */
    private $traverser;



    public function injectParser(Parser $parser)
    {
        $this->parser = $parser;
    }



    public function injectRecursiveFileIterator(RecursiveFileIterator $recursiveFileIterator)
    {
        $this->recursiveFileIterator = $recursiveFileIterator;
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



    public function process(string $path, array $ignores)
    {
        $files = $this->recursiveFileIterator->open('/\.php$/', $path, $ignores);
        foreach ($files as $file) {
            $this->processFile($file, $path);
        }
    }



    /**
     * @todo Find a good name for this method
     */
    private function processFile(SplFileInfo $file, string $path)
    {
        $filename = ltrim(str_replace($path, '', $file->getPathName()), '/');
        $code = file_get_contents((string) $file);

        if (false === $code) {
            echo "Could not read file $file.\n";
        } else {
            $this->analyze($filename, $code);
        }
    }



    private function analyze(string $filename, string $code)
    {
        try {
            $nodes = $this->parser->parse($code);
            $this->traverser->setFilename($filename);
            $this->traverser->traverse($nodes);
        }
        catch (PhpParserError $exception) {
            echo 'Parse Error: ', $exception->getMessage();
        }
    }
}
