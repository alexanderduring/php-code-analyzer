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

            $this->report();
        } else {
            echo "The file/folder " . $path . " does not exist.\n";
        }

        return;
    }



    private function report()
    {
        $this->reportDefinitions();
        $this->reportUsages();
        $this->reportNotices();
    }



    private function reportDefinitions()
    {
        echo "\nFound classes:\n--------------\n";

        $definitionIndex = $this->analyzer->getDefinitionIndex();
        foreach ($definitionIndex->getDefinitions() as $definition) {
            $string = $definition['type'] . " ";
            $string .= $definition['fqn'] . ", ";
            $string .= $definition['file'] . "\n";
            echo $string;
        }

        echo "\n\n";
    }



    private function reportUsages()
    {
        echo "Found instantiations:\n---------------------\n";

        $usageIndex = $this->analyzer->getUsageIndex();
        foreach ($usageIndex->getUsages() as $class => $usages) {
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



    private function reportNotices()
    {
        echo "Notices:\n--------\n";

        $usageIndex = $this->analyzer->getUsageIndex();
        foreach ($usageIndex->getNotices() as $notice) {

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
