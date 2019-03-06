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
        ];

        $defaultExpectations = [
        ];

        $testCases = [
            'Simple class definition' => [
                'preconditions' => [
                    'codeFile' => 'definition-class-global.php'
                ],
                'expectations' => [
                ]
            ],
            'Simple interface definition' => [
                'preconditions' => [
                    'codeFile' => 'definition-interface-global.php'
                ],
                'expectations' => [
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
        $code = file_get_contents(__DIR__ . '/../../ressources/' . $preconditions['codeFile']);
        $testCase = $this->extractTestCase($code);
        $preconditions = $testCase['preconditions'];
        $expectations = $testCase['expectations'];

        $codeAnalyzer = $this->getCodeAnalyzer($preconditions, $expectations);
        $codeAnalyzer->analyze($code, $preconditions['sourceName']);
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
            $source = $preconditions['sourceName'];
            $prophecy->addClass($fqn, $type, [], [], $source, Argument::cetera())->shouldBeCalled();
            $prophecy->addNodeType(Argument::type('string'))->willReturn(true);
            $prophecy->addTypeDeclaration(Argument::cetera())->shouldBeCalled();
        }

        $index = $prophecy->reveal();

        return $index;
    }



    private function extractTestCase(string $testFileContent): array
    {
        $posScriptTag = strpos($testFileContent,'</script>');
        $testCaseString = substr($testFileContent, 0, $posScriptTag+9);
        $testCaseString = str_replace(['<script>', '</script>', 'testcase = '], '', $testCaseString);
        $testCase = json_decode($testCaseString, true);

        return $testCase;
    }
}
