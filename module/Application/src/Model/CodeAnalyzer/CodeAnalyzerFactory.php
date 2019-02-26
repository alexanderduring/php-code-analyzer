<?php

namespace Application\Model\CodeAnalyzer;

use Application\Model\CodeAnalyzer\NodeTraverser\ContextAwareNodeTraverser;
use Application\Model\CodeAnalyzer\NodeVisitor\ClassDefinitionIndexer;
use Application\Model\CodeAnalyzer\NodeVisitor\ClassUsageIndexer;
use Interop\Container\ContainerInterface;
use PhpParser\ParserFactory;
use PhpParser\NodeVisitor\NameResolver;
use Zend\ServiceManager\Factory\FactoryInterface;

class CodeAnalyzerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        // Create CodeAnalyzer
        $codeAnalyzer = new CodeAnalyzer();

        // Inject Index
        $index = new Index();
        $codeAnalyzer->injectIndex($index);

        // Inject Parser
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->create(ParserFactory::PREFER_PHP5);
        $codeAnalyzer->injectParser($parser);

        // Inject Traverser
        $traverser = $this->buildTraverser($index);
        $codeAnalyzer->injectTraverser($traverser);

        return $codeAnalyzer;
    }



    private function buildTraverser(Index $index): ContextAwareNodeTraverser
    {
        $traverser = new ContextAwareNodeTraverser();

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
