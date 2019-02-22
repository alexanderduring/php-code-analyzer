<?php

declare(strict_types=1);

namespace Application\Model\CodeAnalyzer\NodeVisitor;

use PhpParser\NodeVisitorAbstract;

class ContextAwareNodeVisitor extends NodeVisitorAbstract
{
    /** @var string */
    protected $filename = '';



    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }
}
