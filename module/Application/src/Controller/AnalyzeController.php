<?php

namespace Application\Controller;

use Application\Model\ClassName\ClassName;
use Application\Model\CodeAnalyzer\CodeAnalyzer;
use Application\Model\CodeAnalyzer\Index;
use Application\Model\CodeAnalyzer\Index\NamespaceTree;
use EmberDb\DocumentManager;
use Zend\Mvc\Controller\AbstractActionController;

class AnalyzeController extends AbstractActionController
{
    /** @var \Application\Model\CodeAnalyzer\CodeAnalyzer */
    private $analyzer;



    public function injectCodeAnalyzer(CodeAnalyzer $codeAnalyzer)
    {
        $this->analyzer = $codeAnalyzer;
    }



    public function runAction()
    {
        $path = (string) $this->getRequest()->getParam('path');
        $ignores = $this->splitCommaSeparatedValue($this->getRequest()->getParam('ignore'));

        if (file_exists($path)) {
            $this->analyzer->process($path, $ignores);

            $this->storeResults();

            $usedMemory = round(memory_get_usage() / (1024*1024), 2);
            echo "Analyzing finished. Used memory: " . $usedMemory . " MBytes.\n\n";
        } else {
            echo "The file/folder " . $path . " does not exist.\n\n";
        }

        return;
    }



    public function reportAction()
    {
        if (file_exists('data/results/results.json')) {
            $this->reportResults();
        } else {
            echo "Nothing to report.";
        }

        return;
    }



    private function storeResults()
    {
        // Setup Ember Db
        $documentManager = new DocumentManager();
        $documentManager->setDatabasePath('data/results');

        $index = $this->analyzer->getIndex();
        $definitions = $index->getDefinitions();
        $usages = $index->getUsages();
        $notices = $index->getNotices();
        $namespaces = $index->getNamespaces();

        // Insert class definitions
        $documentManager->remove('classes');

        foreach ($definitions as $definition) {
            $fullyQualifiedClassname = $definition['name']['fqn'];
            if (array_key_exists($fullyQualifiedClassname, $usages)) {
                $definition['usages'] = $usages[$fullyQualifiedClassname];
            }
            $documentManager->insert('classes', $definition);
        }

        // Create and insert namespace tree
        $classes = $documentManager->find('classes');
        $namespaceTree = new NamespaceTree();
        foreach ($classes as $class) {
            $className = new ClassName($class->get('name.fqn'));
            $namespaceTree->addClass($className->getNamespaceAsArray());
        }
        $documentManager->remove('namespaceTree');
        $documentManager->insert('namespaceTree', $namespaceTree->toArray());

        // List of namespaces
        $documentManager->remove('namespaces');
        foreach ($namespaces as $namespaceName => $namespaceData) {
            $namespaceData['subNamespaces'] = array_keys($namespaceData['subNamespaces']);
            $documentManager->insert('namespaces', $namespaceData);
        }

        $results = array(
            'definitions' => $definitions,
            'usages' => $usages,
            'namespaces' => $namespaces,
            'notices' => $notices
        );

        file_put_contents('data/results/results.json', json_encode($results));
        //var_dump($index->getNamespaces());
    }



    private function reportResults()
    {
        if (file_exists('data/results/results.json')) {
            $results = json_decode(file_get_contents('data/results/results.json'), true);
            $this->reportDefinitions($results['definitions']);
            $this->reportUsages($results['usages']);
            $this->reportNotices($results['notices']);
        }
    }



    private function reportDefinitions($definitions)
    {
        echo "\nFound classes:\n--------------\n";

        foreach ($definitions as $definition) {
            $type = $definition['type'];
            $fqn = $definition['name']['fqn'];
            $file = $definition['file'];
            $start = $definition['startLine'];
            $end = $definition['endLine'];

            $string = "$type $fqn, $file (line: $start-$end)\n";
            echo $string;
        }

        echo "\n\n";
    }



    private function reportUsages($usages)
    {
        echo "Found instantiations:\n---------------------\n";

        foreach ($usages as $class => $usages) {

            if (array_key_exists('new', $usages)) {
                echo "Instantiations of " . $class . ":\n";

                foreach ($usages['new'] as $instantiation) {
                    $text = "  in " . $instantiation['context'];
                    $text .= " (file: " . $instantiation['file'] . ", line: " . $instantiation['startLine'] .")\n";
                    echo $text;
                }

                echo "\n";
            }
        }

        echo "\n";
    }



    private function reportNotices($notices)
    {
        echo "Notices:\n--------\n";

        foreach ($notices as $notice) {

            switch ($notice['type']) {
                case Index::NOTICE_NEW_WITH_VARIABLE:
                    $string = "New with variable (new " . $notice['variable'] . ")";
                    break;
                case Index::NOTICE_UNKNOWN_NEW:
                    $string = "New with unknown structure (" . $notice['nodeType'] . ")";
                    break;
                case Index::NOTICE_CONST_FETCH_WITH_VARIABLE:
                    $string = "Const fetch with variable (" . $notice['variable'] . ")";
                    break;
                case Index::NOTICE_STATC_CALL_WITH_VARIABLE:
                    $string = "Static call with variable (" . $notice['variable'] . ")";
                    break;
                default:
                    $string = "Unknown notice";
            }

            $string .= " in " . $notice['context'];
            $string .= " (" . $notice['file'] . ", line " . $notice['startLine'] . "-" . $notice['endLine'] . ")\n";

            echo $string;
        }

        echo "\n";
    }



    private function splitCommaSeparatedValue(string $value = null): array
    {
        if (is_null($value)) {
            $entries = [];
        } else {
            $valueString = (string) $value;
            $cleanedString = str_replace(' ', '', $valueString);

            if (strlen($cleanedString) > 0) {
                $entries = explode(',', $cleanedString);
            } else {
                $entries = [];
            }
        }

        return $entries;
    }
}