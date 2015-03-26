<?php

namespace Application\Controller;

use Application\Model\CodeAnalyzer\CodeAnalyzer;
use Zend\Mvc\Controller\AbstractActionController;

class AnalyzeController extends AbstractActionController
{
    public function runAction()
    {
        $code = file_get_contents('data/code/test.php');

        $analyzer = new CodeAnalyzer();
        $analyzer->analyze($code);

        return;
    }
}
