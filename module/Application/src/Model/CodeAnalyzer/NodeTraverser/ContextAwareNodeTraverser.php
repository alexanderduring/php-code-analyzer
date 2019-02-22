<?php

declare(strict_types=1);

namespace Application\Model\CodeAnalyzer\NodeTraverser;

use Application\Model\CodeAnalyzer\NodeVisitor\ContextAwareNodeVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;

class ContextAwareNodeTraverser extends NodeTraverser
{
    /** @var string */
    protected $filename = '';



    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }



    /**
     * Traverses an array of nodes using the registered visitors.
     *
     * @param Node[] $nodes Array of nodes
     * @return Node[] Traversed array of nodes
     */
    public function traverse(array $nodes) : array
    {
        $this->setFilenameToVisitors();
        $nodes = parent::traverse($nodes);

        return $nodes;
    }



    private function setFilenameToVisitors()
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor instanceof ContextAwareNodeVisitor) {
                $visitor->setFilename($this->filename);
            }
        }
    }
}
