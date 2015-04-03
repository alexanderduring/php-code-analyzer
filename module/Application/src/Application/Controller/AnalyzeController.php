<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class AnalyzeController extends AbstractActionController
{
    /** @var Application\Model\CodeAnalyzer\CodeAnalyzer */
    private $analyzer;



    public function runAction()
    {
        $code = file_get_contents('data/code/test.php');

        $this->analyzer = $this->getServiceLocator()->get('CodeAnalyzer');
        $this->analyzer->processDirectory('data/code');

        $this->analyzer->report();

        return;
    }
}
