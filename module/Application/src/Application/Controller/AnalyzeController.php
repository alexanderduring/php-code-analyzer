<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class AnalyzeController extends AbstractActionController
{
    /** @var Application\Model\CodeAnalyzer\CodeAnalyzer */
    private $analyzer;



    public function runAction()
    {
        $path = $this->getRequest()->getParam('path');

        if (file_exists($path)) {
            $this->analyzer = $this->getServiceLocator()->get('CodeAnalyzer');
            $this->analyzer->process($path);

            $this->analyzer->report();
        } else {
            echo "The file/folder " . $path . " does not exist.\n";
        }

        return;
    }
}
