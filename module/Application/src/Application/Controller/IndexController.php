<?php

namespace Application\Controller;

use EmberDb\DocumentManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $fqnParam = $this->getEvent()->getRouteMatch()->getParam('fqn');
        $fqn = urldecode($fqnParam);

        // Setup Ember Db
        $documentManager = new DocumentManager();
        $documentManager->setDatabasePath('data/results');

        $namespaces = $documentManager->find('namespaces');

        $names = [];
        $amounts = [];
        foreach ($namespaces as $namespace) {
            $names[] = $namespace->get('name.fqn');
            $amounts[] = $namespace->get('allDescendents');
        }

        return array(
            'fqn' => $fqn,
            'namespaces' => $namespaces,
            'names' => $names,
            'amounts' => $amounts
        );
    }



    public function d3BarChartOneAction()
    {
    }



    public function d3BarChartTwoAction()
    {
    }



    public function d3DoughnutChartAction()
    {
    }



    public function d3PartitionChartAction()
    {
    }



    public function d3SunburstChartAction()
    {
    }



    public function getDataAction()
    {
        $length = rand(1, 20);
        $data = array();
        for ($i = 1; $i <= $length; $i++) {
            $data[] = rand(1, 100);
        }

        return new JsonModel($data);
    }



    public function classesAction()
    {
        $fqnParam = $this->getEvent()->getRouteMatch()->getParam('fqn');
        $fqn = urldecode($fqnParam);

        // Setup Ember Db
        $documentManager = new DocumentManager();
        $documentManager->setDatabasePath('data/results');

        $classes = $documentManager->find('classes');

        return array(
            'fqn' => $fqn,
            'classes' => $classes
        );
    }



    public function getNamespacesAction()
    {
        // Setup Ember Db
        $documentManager = new DocumentManager();
        $documentManager->setDatabasePath('data/results');

        $foundEntries = $documentManager->find('namespaces');
        $namespaces = $foundEntries[0];

        $jsonModel = new JsonModel($namespaces->toArray());

        return $jsonModel;
    }
}
