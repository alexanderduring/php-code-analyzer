<?php

declare(strict_types=1);

namespace Application\Model\CodeAnalyzer\NodeTraverser;

use Application\Model\CodeAnalyzer\Context\Context;
use Application\Model\CodeAnalyzer\NodeVisitor\ContextAwareNodeVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;

class ContextAwareNodeTraverser extends NodeTraverser
{
    /** @var Context */
    protected $context;



    public function setContext(Context $context)
    {
        $this->context = $context;
    }



    /**
     * Traverses an array of nodes using the registered visitors.
     *
     * @param Node[] $nodes Array of nodes
     * @return Node[] Traversed array of nodes
     */
    public function traverse(array $nodes) : array
    {
        $this->setContextToVisitors();
        $nodes = parent::traverse($nodes);

        return $nodes;
    }



    private function setContextToVisitors()
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor instanceof ContextAwareNodeVisitor) {
                $visitor->setContext($this->context);
            }
        }
    }
}
