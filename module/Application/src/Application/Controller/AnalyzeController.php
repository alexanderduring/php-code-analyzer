<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class AnalyzeController extends AbstractActionController
{
    public function runAction()
    {
        $code = file_get_contents('data/code/test.php');

        $analyzer = $this->getServiceLocator()->get('CodeAnalyzer');
        $analyzer->analyze($code);

        $analyzer->report();

        return;
    }
}
