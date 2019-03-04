<?php

declare(strict_types=1);

namespace Application\Model\CodeAnalyzer;

use Application\Model\CodeAnalyzer\NodeTraverser\ContextAwareNodeTraverser;
use Application\Model\CodeAnalyzer\NodeVisitor\ClassDefinitionIndexer;
use Application\Model\CodeAnalyzer\NodeVisitor\ClassUsageIndexer;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class CodeAnalyzerTest extends TestCase
{
    static public function providerAnalyze()
    {
        $defaultPreconditions = [
            'source' => 'test-source.txt'
        ];

        $defaultExpectations = [
        ];

        $testCases = [
            'Simple class definition' => [
                'preconditions' => [
                    'code' => '<?php
                        class Foo
                        {
                            public function __construct(Bar $bar) {
                                echo $bar;
                            }
                        }'
                ],
                'expectations' => [
                    'classDefinitions' => [
                        'foundClasses' => [
                            [
                                'fqn' => ['Foo'],
                                'type' => 'class'
                            ]
                        ]
                    ]
                ]
            ],
            'Simple interface definition' => [
                'preconditions' => [
                    'code' => '<?php
                        interface Bar
                        {
                            public function __construct(Bar $bar);
                        }'
                ],
                'expectations' => [
                    'classDefinitions' => [
                        'foundClasses' => [
                            [
                                'fqn' => ['Bar'],
                                'type' => 'interface'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Merge test data with default data
        foreach ($testCases as &$testCase) {
            $testCase['preconditions'] = array_merge($defaultPreconditions, $testCase['preconditions']);
            $testCase['expectations'] = array_merge($defaultExpectations, $testCase['expectations']);
        }

        return $testCases;
    }



    /**
     * @dataProvider providerAnalyze
     */
    public function testAnalyze(array $preconditions, array $expections)
    {
        $codeAnalyzer = $this->getCodeAnalyzer($preconditions, $expections);
        $codeAnalyzer->analyze($preconditions['code'], $preconditions['source']);
    }



    private function getCodeAnalyzer(array $preconditions, array $expectations): CodeAnalyzer
    {
        $codeAnalyzer = new CodeAnalyzer();

        // Inject Index
        $index = $this->getIndex($preconditions, $expectations);
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



    private function getIndex(array $preconditions, array $expectations): Index
    {
        /** @var Index|ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(Index::class);

        foreach ($expectations['classDefinitions']['foundClasses'] as $class) {
            $fqn = $class['fqn'];
            $type = $class['type'];
            $source = $preconditions['source'];
            $prophecy->addClass($fqn, $type, [], [], $source, Argument::cetera())->shouldBeCalled();
            $prophecy->addNodeType(Argument::type('string'))->willReturn(true);
            $prophecy->addTypeDeclaration(Argument::cetera())->shouldBeCalled();
        }

        $index = $prophecy->reveal();

        return $index;
    }
}
