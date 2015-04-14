<?php

namespace Application\Model\CodeAnalyzer;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Model\CodeAnalyzer\Index;
use Application\Model\CodeAnalyzer\NodeVisitor\ClassDefinitionIndexer;
use Application\Model\CodeAnalyzer\NodeVisitor\ClassUsageIndexer;
use PhpParser\Parser;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

class CodeAnalyzerFactory implements FactoryInterface
{
    /**
     * @param Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return Application\Model\CodeAnalyzer\CodeAnalyzer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Create CodeAnalyzer
        $codeAnalyzer = new CodeAnalyzer();

        // Inject Index
        $index = new Index();
        $codeAnalyzer->injectIndex($index);

        // Inject Parser
        $parser = new Parser(new Lexer());
        $codeAnalyzer->injectParser($parser);

        // Inject Traverser
        $traverser = $this->buildTraverser($index);
        $codeAnalyzer->injectTraverser($traverser);

        return $codeAnalyzer;
    }



    /**
     * @param Index $index
     * @return NodeTraverser
     */
    private function buildTraverser(Index $index)
    {
        $traverser = new NodeTraverser();

        // Add NameResolver to handle namespaces
        $nameResolver = new NameResolver();
        $traverser->addVisitor($nameResolver);

        // Add ClassDefinitionIndexer
        $classDefinitionIndexer = new ClassDefinitionIndexer();
        $classDefinitionIndexer->injectIndex($index);
        $traverser->addVisitor($classDefinitionIndexer);

        // Add ClassUsageIndexer
        $classUsageIndexer = new ClassUsageIndexer();
        $classUsageIndexer->injectIndex($index);
        $traverser->addVisitor($classUsageIndexer);

        return $traverser;
    }
}
