<?php

namespace Application\Model\CodeAnalyzer;

use RecursiveFilterIterator as SplRecursiveFilterIterator;

class RecursiveFilterIterator extends SplRecursiveFilterIterator
{
    private $ignores = array();



    public function setIgnores($ignores)
    {
        $this->ignores = $ignores;
    }



    public function accept()
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
        $children = new self($this->getInnerIterator()->getChildren());
        $children->setIgnores($this->ignores);

        return $children;
    }
}
