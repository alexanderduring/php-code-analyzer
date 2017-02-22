<?php

namespace Application\Controller;

use EmberDb\DocumentManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

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
            $amounts[] = $namespace->get('countAllDescendents');
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



    public function d3LineChartAction()
    {
    }



    public function d3LineChartDataAction()
    {

        $data = [
            [
                'id' => 'Max.',
                'values' => [
                    ['date' => '2017-01-29', 'temperature' => 6.1],
                    ['date' => '2017-01-30', 'temperature' => 4.2],
                    ['date' => '2017-01-31', 'temperature' => 2.0],
                    ['date' => '2017-02-01', 'temperature' => 2.1]
                ]
            ],
            [
                'id' => 'Min.',
                'values' => [
                    ['date' => '2017-01-29', 'temperature' => -1.8],
                    ['date' => '2017-01-30', 'temperature' => 0.4],
                    ['date' => '2017-01-31', 'temperature' => 0.7],
                    ['date' => '2017-02-01', 'temperature' => 0.1]
                ]
            ]
        ];

        $view = new JsonModel($data);

        return $view;
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
        $length = rand(1, 12);
        $data = array();
        for ($i = 1; $i <= $length; $i++) {
            $data[] = [
                'label' => 'foo_' . rand(1, 100),
                'count' => rand(1, 100)
            ];
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

        $foundEntries = $documentManager->find('namespaceTree');
        $namespaces = $foundEntries[0];

        $jsonModel = new JsonModel($namespaces->toArray());

        return $jsonModel;
    }
}
