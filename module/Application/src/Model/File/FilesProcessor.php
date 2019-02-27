<?php

declare(strict_types=1);

namespace Application\Model\File;

use Application\Model\CodeAnalyzer\CodeAnalyzer;
use SplFileInfo;
use Traversable;

class FilesProcessor
{
    public function processFiles(Traversable $files, CodeAnalyzer $codeAnalyzer)
    {
        foreach ($files as $file) {
            /** @var SplFileInfo $file */

            $code = file_get_contents((string) $file);
            if (false === $code) {
                echo "Could not read file $file.\n";
            } else {
                $trimmedFilepath = ltrim($file->getPathName(), '/');
                $codeAnalyzer->analyze($code, $trimmedFilepath);
            }
        }
    }
}
