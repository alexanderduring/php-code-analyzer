<?php

namespace Application\Model\CodeAnalyzer;

use Application\Model\CodeAnalyzer\NodeTraverser\ContextAwareNodeTraverser;
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



    public function process(string $path, array $ignores)
    {
        $realPath = realpath($path);

        if (false === $realPath) {
            echo "Could not find path $path.\n";
        } else {
            if (is_dir($realPath)) {
                $this->processDirectory($realPath, $ignores);
            } else {
                $this->processFile($realPath);
            }
        }

    }



    private function processFile(string $path)
    {
        $file = new SplFileInfo($path);
        $this->foo($file, $path);
    }



    private function processDirectory(string $path, array $ignores)
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
     */
    private function foo(SplFileInfo $file, string $path)
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
