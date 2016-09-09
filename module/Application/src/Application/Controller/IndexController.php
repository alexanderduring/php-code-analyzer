<?php

namespace Application\Controller;

use EmberDb\DocumentManager;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        // Setup Ember Db
        $documentManager = new DocumentManager();
        $documentManager->setDatabasePath('data/results');

        $classes = $documentManager->find('classes');

        return array(
            'classes' => $classes
        );
    }
}
