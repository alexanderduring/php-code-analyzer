<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class AnalyzeController extends AbstractActionController
{
    /** @var \Application\Model\CodeAnalyzer\CodeAnalyzer */
    private $analyzer;



    public function runAction()
    {
        $path = $this->getRequest()->getParam('path');

        if (file_exists($path)) {
            $this->analyzer = $this->getServiceLocator()->get('CodeAnalyzer');
            $this->analyzer->process($path);

            $this->storeResults();
            $this->reportResults();
        } else {
            echo "The file/folder " . $path . " does not exist.\n";
        }

        return;
    }



    private function storeResults()
    {
        $definitionIndex = $this->analyzer->getDefinitionIndex();
        $definitions = $definitionIndex->getDefinitions();

        $usageIndex = $this->analyzer->getUsageIndex();
        $usages = $usageIndex->getUsages();
        $notices = $usageIndex->getNotices();

        $results = array(
            'definitions' => $definitions,
            'usages' => $usages,
            'notices' => $notices
        );

        file_put_contents('data/results/results.json', json_encode($results));
    }



    private function reportResults()
    {
        if (file_exists('data/results/results.json')) {
            $results = json_decode(file_get_contents('data/results/results.json'), true);
            $this->reportDefinitions($results['definitions']);
            $this->reportUsages($results['usages']);
            $this->reportNotices($results['notices']);
        } else {
            echo "Nothing to report.";
        }
    }



    private function reportDefinitions($definitions)
    {
        echo "\nFound classes:\n--------------\n";

        foreach ($definitions as $definition) {
            $string = $definition['type'] . " ";
            $string .= $definition['fqn'] . ", ";
            $string .= $definition['file'] . "\n";
            echo $string;
        }

        echo "\n\n";
    }



    private function reportUsages($usages)
    {
        echo "Found instantiations:\n---------------------\n";

        foreach ($usages as $class => $usages) {
            echo "Instantiations of " . $class . ":\n";

            foreach ($usages['new'] as $instantiation) {
                $text = "  in " . $instantiation['context'];
                $text .= " (file: " . $instantiation['file'] . ", line: " . $instantiation['line'] .")\n";
                echo $text;
            }

            echo "\n";
        }

        echo "\n";
    }



    private function reportNotices($notices)
    {
        echo "Notices:\n--------\n";

        foreach ($notices as $notice) {

            switch ($notice['type']) {
                case \Application\Model\CodeAnalyzer\UsageIndex::NOTICE_NEW_WITH_VARIABLE:
                    $string = "New with variable (new " . $notice['variable'] . ")";
                    break;
                case \Application\Model\CodeAnalyzer\UsageIndex::NOTICE_UNKNOWN_NEW:
                    $string = "New with unknown structure (" . $notice['nodeType'] . ")";
                    break;
            }

            $string .= " in " . $notice['context'];
            $string .= " (" . $notice['file'] . ", line " . $notice['line'] . ")\n";

            echo $string;
        }

        echo "\n";
    }
}
