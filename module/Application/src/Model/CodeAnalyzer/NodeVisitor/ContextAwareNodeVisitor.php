<?php

declare(strict_types=1);

namespace Application\Model\CodeAnalyzer\NodeVisitor;

use Application\Model\CodeAnalyzer\Context\Context;
use PhpParser\NodeVisitorAbstract;

class ContextAwareNodeVisitor extends NodeVisitorAbstract
{
    /** @var Context */
    protected $context;



    public function setContext(Context $context)
    {
        $this->context = $context;
    }
}
