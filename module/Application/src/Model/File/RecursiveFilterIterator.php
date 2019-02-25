<?php

namespace Application\Model\File;

use RecursiveFilterIterator as SplRecursiveFilterIterator;
use RecursiveIterator;

class RecursiveFilterIterator extends SplRecursiveFilterIterator
{
    private $ignores = array();



    public function setIgnores(array $ignores)
    {
        $this->ignores = $ignores;
    }



    public function accept(): bool
    {
        $matchesIgnores = false;
        $filePath = $this->current()->getPathname();

        foreach ($this->ignores as $ignore) {
            if (strpos($filePath, $ignore) !== false) {
                $matchesIgnores = true;
                break;
            }
        }

        $accept = !$matchesIgnores;

        return $accept;
    }



    public function getChildren()
    {
        $innerIterator = $this->getInnerIterator();

        if ($innerIterator instanceof RecursiveIterator) {
            $children = new self($innerIterator->getChildren());
            $children->setIgnores($this->ignores);
        } else {
            $children = null;
        }

        return $children;
    }
}
