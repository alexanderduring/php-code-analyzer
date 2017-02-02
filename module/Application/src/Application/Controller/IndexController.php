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
        $header = ['Food', 'Deliciousness'];
        $data = [
            ['Apples', 9],
            ['Green Beans', 5],
            ['Egg Salad Sandwich', 4],
            ['Cookies', 10],
            ['Vegemite', 0.2],
            ['Burrito', 7]
        ];

        $header = ['date', 'New York', 'San Francisco', 'Austin'];
        $data = [
            ['2011-10-01', 63.4, 62.7, 72.2],
            ['2011-10-02', 58.0, 59.9, 67.7],
            ['2011-10-03', 53.3, 59.1 ,69.4],
            ['2011-10-04', 55.7, 58.8 ,68.0]
        ];

        $data = [
            [
                'id' => 'New York',
                'values' => [
                    ['date' => '2011-10-01', 'temperature' => 63.4],
                    ['date' => '2011-10-02', 'temperature' => 58.4],
                    ['date' => '2011-10-03', 'temperature' => 53.4],
                    ['date' => '2011-10-04', 'temperature' => 55.4]
                ]
            ],
            [
                'id' => 'San Francisco',
                'values' => [
                    ['date' => '2011-10-01', 'temperature' => 62.7],
                    ['date' => '2011-10-02', 'temperature' => 59.9],
                    ['date' => '2011-10-03', 'temperature' => 59.1],
                    ['date' => '2011-10-04', 'temperature' => 58.8]
                ]
            ],
            [
                'id' => 'Austin',
                'values' => [
                    ['date' => '2011-10-01', 'temperature' => 72.2],
                    ['date' => '2011-10-02', 'temperature' => 67.7],
                    ['date' => '2011-10-03', 'temperature' => 69.4],
                    ['date' => '2011-10-04', 'temperature' => 68.0]
                ]
            ]
        ];

        $view = new ViewModel();
        $view->setTerminal(true);

        $view->setVariable('header', $header);
        $view->setVariable('data', $data);

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

        $foundEntries = $documentManager->find('namespaceTree');
        $namespaces = $foundEntries[0];

        $jsonModel = new JsonModel($namespaces->toArray());

        return $jsonModel;
    }
}
