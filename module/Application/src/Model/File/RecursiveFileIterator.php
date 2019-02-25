<?php

declare(strict_types=1);

namespace Application\Model\File;

use ArrayObject;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;
use Traversable;

class RecursiveFileIterator
{
    public function open(string $regexPattern, string $path, array $ignores): Traversable
    {
        $realPath = realpath($path);

        if (false === $realPath) {
            $files = new ArrayObject([]);
        } else {
            if (is_dir($realPath)) {
                $files = $this->openDir($regexPattern, $realPath, $ignores);
            } else {
                $file = $this->openFile($realPath);
                $files = new ArrayObject([$file]);
            }
        }

        return $files;
    }



    private function openDir(string $regexPattern, string $path, array $ignores): RegexIterator
    {
        $directoryIterator = new RecursiveDirectoryIterator($path);

        $filterIterator = new RecursiveFilterIterator($directoryIterator);
        $filterIterator->setIgnores($ignores);

        $recursiveIteratorIterator = new RecursiveIteratorIterator($filterIterator);

        return new RegexIterator($recursiveIteratorIterator, $regexPattern);
    }



    private function openFile(string $path): SplFileInfo
    {
        return new SplFileInfo($path);
    }
}
