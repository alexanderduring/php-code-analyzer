<?php

namespace Application\Model\CodeAnalyzer;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Model\CodeAnalyzer\DefinitionIndex;
use Application\Model\CodeAnalyzer\UsageIndex;
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

        // Inject DefinitionIndex
        $definitionIndex = new DefinitionIndex();
        $codeAnalyzer->injectDefinitionIndex($definitionIndex);

        // Inject UsageIndex
        $usageIndex = new UsageIndex();
        $codeAnalyzer->injectUsageIndex($usageIndex);

        // Inject Parser
        $parser = new Parser(new Lexer());
        $codeAnalyzer->injectParser($parser);

        // Inject Traverser
        $traverser = $this->buildTraverser($definitionIndex, $usageIndex);
        $codeAnalyzer->injectTraverser($traverser);

        return $codeAnalyzer;
    }



    /**
     * @param DefinitionIndex $definitionIndex
     * @param UsageIndex $usageIndex
     * @return NodeTraverser
     */
    private function buildTraverser(DefinitionIndex $definitionIndex, UsageIndex $usageIndex)
    {
        $traverser = new NodeTraverser();

        // Add NameResolver to handle namespaces
        $nameResolver = new NameResolver();
        $traverser->addVisitor($nameResolver);

        // Add ClassDefinitionIndexer
        $classDefinitionIndexer = new ClassDefinitionIndexer();
        $classDefinitionIndexer->injectIndex($definitionIndex);
        $traverser->addVisitor($classDefinitionIndexer);

        // Add ClassUsageIndexer
        $classUsageIndexer = new ClassUsageIndexer();
        $classUsageIndexer->injectIndex($usageIndex);
        $traverser->addVisitor($classUsageIndexer);

        return $traverser;
    }
}
