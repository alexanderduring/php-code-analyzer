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

        $testCaseFiles =[
            'definition-class-global.php',
            'definition-interface-global.php'
        ];

        foreach ($testCaseFiles as $testCaseFile) {
            $fileContent = file_get_contents(__DIR__ . '/../../ressources/' . $testCaseFile);

            // Extract test case data
            $posScriptTag = strpos($fileContent,'</script>');
            $testCaseString = substr($fileContent, 0, $posScriptTag + 9);
            $testCaseString = str_replace(['<script>', '</script>', 'testcase = '], '', $testCaseString);
            $testCase = json_decode($testCaseString, true);

            // Assigning the code to be analyzed
            $posPhpTag = strpos($fileContent, '<?php');
            $code = substr($fileContent, $posPhpTag);
            $testCase['preconditions']['code'] = $code;

            // Merge test case with default settings
            $testCase['preconditions'] = array_merge($defaultPreconditions, $testCase['preconditions']);
            $testCase['expectations'] = array_merge($defaultExpectations, $testCase['expectations']);

            yield $testCase;
        }
    }



    /**
     * @dataProvider providerAnalyze
     */
    public function testAnalyze(array $preconditions, array $expectations)
    {
        $codeAnalyzer = $this->getCodeAnalyzer($preconditions, $expectations);
        $codeAnalyzer->analyze($preconditions['code'], $preconditions['sourceName']);
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
            $startLine = $class['lines'][0];
            $endLine = $class['lines'][1];
            $source = $preconditions['sourceName'];

            $prophecy->addClass($fqn, $type, [], [], $source, $startLine, $endLine)->shouldBeCalled();
            $prophecy->addTypeDeclaration(Argument::cetera())->shouldBeCalled();
        }

        $index = $prophecy->reveal();

        return $index;
    }
}
